<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';
requireRole(['admin', 'supervisor']); // Restricted to management

$dispatch_id = $_GET['id'] ?? null;
if (!$dispatch_id) {
    header("Location: pending_routes.php");
    exit;
}

// Handle AJAX Cut-off
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax_action']) && $_POST['ajax_action'] == 'process_cutoff') {
    header('Content-Type: application/json');
    $order_id = $_POST['order_id'];
    $cutoffs = $_POST['cutoffs'] ?? [];
    
    try {
        $pdo->beginTransaction();
        
        $orderStmt = $pdo->prepare("SELECT total_amount FROM orders WHERE id = ?");
        $orderStmt->execute([$order_id]);
        $order = $orderStmt->fetch();
        if (!$order) throw new Exception("Order not found");

        $itemStmt = $pdo->prepare("SELECT id, quantity, selling_price FROM order_items WHERE order_id = ? AND product_id = ?");
        $updateItemStmt = $pdo->prepare("UPDATE order_items SET quantity = quantity - ? WHERE id = ?");
        $updateStockStmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        
        $total_deduction = 0;

        foreach ($cutoffs as $pid => $qty) {
            $qty = (int)$qty;
            if ($qty > 0) {
                $itemStmt->execute([$order_id, $pid]);
                $item = $itemStmt->fetch();
                if ($item && $item['quantity'] >= $qty) {
                    $updateItemStmt->execute([$qty, $item['id']]);
                    $updateStockStmt->execute([$qty, $pid]);
                    $total_deduction += ($qty * $item['selling_price']);
                } else {
                    throw new Exception("Invalid cutoff quantity for product ID " . $pid);
                }
            }
        }
        
        // Remove items with 0 qty
        $pdo->prepare("DELETE FROM order_items WHERE order_id = ? AND quantity <= 0")->execute([$order_id]);
        
        // Update order total
        $new_total = $order['total_amount'] - $total_deduction;
        $pdo->prepare("UPDATE orders SET total_amount = ? WHERE id = ?")->execute([$new_total, $order_id]);
        
        $pdo->commit();
        echo json_encode(['success' => true, 'new_total' => $new_total]);
    } catch(Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle Finalization
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'finalize_dispatch') {
    $cash = $_POST['cash_collected'] ?: 0;
    $cheque = $_POST['cheque_amount'] ?: 0;
    
    $pdo->beginTransaction();
    // Mark dispatch as completed
    $upd = $pdo->prepare("UPDATE delivery_dispatches SET cash_collected = ?, cheque_amount = ?, status = 'completed' WHERE id = ?");
    $upd->execute([$cash, $cheque, $dispatch_id]);
    
    // Mark all orders as delivered
    $pdo->prepare("UPDATE orders SET order_status = 'delivered' WHERE dispatch_id = ?")->execute([$dispatch_id]);
    
    // Mark all bound rep sessions as completed (or they can stay 'dispatched')
    $pdo->prepare("UPDATE rep_sessions SET status = 'completed' WHERE id IN (SELECT session_id FROM dispatch_sessions WHERE dispatch_id = ?)")->execute([$dispatch_id]);
    
    $pdo->commit();
    header("Location: dispatch.php?id=" . $dispatch_id);
    exit;
}

// Fetch Dispatch Info
$stmt = $pdo->prepare("
    SELECT d.*, dr.name as driver_name, h.name as helper_name
    FROM delivery_dispatches d
    LEFT JOIN employees dr ON d.driver_id = dr.id
    LEFT JOIN employees h ON d.partner_id = h.id
    WHERE d.id = ?
");
$stmt->execute([$dispatch_id]);
$dispatch = $stmt->fetch();

if (!$dispatch) { echo "Dispatch not found."; exit; }
if ($dispatch['status'] == 'completed') {
    header("Location: dispatch.php?id=" . $dispatch_id); exit;
}

// Fetch Orders for this dispatch
$ordersStmt = $pdo->prepare("
    SELECT o.id, c.name as customer_name, o.total_amount, o.paid_amount, o.payment_method
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    WHERE o.dispatch_id = ?
");
$ordersStmt->execute([$dispatch_id]);
$orders = $ordersStmt->fetchAll();

// Fetch Items for Cutoff modal
$itemsStmt = $pdo->prepare("
    SELECT oi.order_id, oi.product_id, p.name, oi.quantity, oi.selling_price
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id IN (SELECT id FROM orders WHERE dispatch_id = ?)
");
$itemsStmt->execute([$dispatch_id]);
$all_items = $itemsStmt->fetchAll(PDO::FETCH_GROUP); // Groups by order_id
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unload & Finalize Dispatch - ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .cutoff-input { width: 80px; text-align: center; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-4">
        <a href="dispatch.php?id=<?php echo $dispatch_id; ?>" class="btn btn-light btn-sm mb-3"><i class="bi bi-arrow-left"></i> Back to Dispatch</a>
        
        <h4 class="fw-bold mb-4">Unload & Finalize Dispatch: <?php echo htmlspecialchars($dispatch['dispatch_ref']); ?></h4>

        <div class="row">
            <!-- Left Column: Cut-Offs -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-2">
                        <h6 class="fw-bold mb-0">Customer Invoices & Cut-Offs</h6>
                        <p class="text-muted small mb-0">Process refused items before finalizing the handover.</p>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Total Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($orders as $o): ?>
                                    <tr>
                                        <td class="font-monospace text-muted">#<?php echo str_pad($o['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($o['customer_name']); ?></td>
                                        <td class="font-monospace fw-bold text-dark" id="total_<?php echo $o['id']; ?>">Rs <?php echo number_format($o['total_amount'], 2); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger" onclick="openCutoffModal(<?php echo $o['id']; ?>)">
                                                <i class="bi bi-scissors"></i> Cut-Off
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Finalization -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white border-0 py-3">
                        <h6 class="fw-bold mb-0 text-uppercase letter-spacing-1">Driver Handover</h6>
                    </div>
                    <div class="card-body bg-light">
                        <form method="POST" onsubmit="return confirm('Finalizing will mark all orders as delivered and close this dispatch. Continue?');">
                            <input type="hidden" name="action" value="finalize_dispatch">
                            
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">Cash Handed Over (Rs)</label>
                                <input type="number" step="0.01" name="cash_collected" class="form-control form-control-lg font-monospace fw-bold text-success" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted small fw-bold">Cheques Total Amount (Rs)</label>
                                <input type="number" step="0.01" name="cheque_amount" class="form-control form-control-lg font-monospace fw-bold text-primary" required>
                            </div>

                            <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold shadow-sm">
                                <i class="bi bi-check-circle me-2"></i> Finalize Dispatch
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cutoff Modal -->
    <div class="modal fade" id="cutoffModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Process Cut-Offs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <form id="cutoffForm">
                        <input type="hidden" id="cutoffOrderId" name="order_id">
                        <input type="hidden" name="ajax_action" value="process_cutoff">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Product</th>
                                    <th class="text-center">Billed Qty</th>
                                    <th class="text-end pe-3">Cut-Off Qty</th>
                                </tr>
                            </thead>
                            <tbody id="cutoffItemsBody">
                                <!-- Populated via JS -->
                            </tbody>
                        </table>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger fw-bold" onclick="submitCutoff()"><i class="bi bi-scissors me-1"></i> Apply Cut-Offs</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const allItems = <?php echo json_encode($all_items); ?>;
        const cutoffModal = new bootstrap.Modal(document.getElementById('cutoffModal'));

        function openCutoffModal(orderId) {
            document.getElementById('cutoffOrderId').value = orderId;
            const items = allItems[orderId] || [];
            let html = '';
            
            items.forEach(item => {
                html += `
                    <tr>
                        <td class="ps-3 fw-bold align-middle">${item.name}<br><small class="text-muted font-monospace">Rs ${parseFloat(item.selling_price).toFixed(2)}</small></td>
                        <td class="text-center align-middle font-monospace fs-5">${item.quantity}</td>
                        <td class="text-end pe-3 align-middle">
                            <input type="number" min="0" max="${item.quantity}" class="form-control d-inline-block cutoff-input font-monospace fw-bold" name="cutoffs[${item.product_id}]" value="0">
                        </td>
                    </tr>
                `;
            });

            if(items.length === 0) {
                html = '<tr><td colspan="3" class="text-center text-muted py-4">No items found or all items removed.</td></tr>';
            }

            document.getElementById('cutoffItemsBody').innerHTML = html;
            cutoffModal.show();
        }

        async function submitCutoff() {
            const form = document.getElementById('cutoffForm');
            const formData = new FormData(form);
            
            let hasCutoff = false;
            for (let [key, value] of formData.entries()) {
                if (key.startsWith('cutoffs[') && parseInt(value) > 0) {
                    hasCutoff = true; break;
                }
            }

            if (!hasCutoff) {
                alert("Please enter at least one cut-off quantity.");
                return;
            }

            if(!confirm("Are you sure? This will permanently deduct items from the invoice and restore them to main inventory stock.")) return;

            try {
                const response = await fetch('unload.php?id=<?php echo $dispatch_id; ?>', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if(result.success) {
                    const orderId = document.getElementById('cutoffOrderId').value;
                    document.getElementById('total_' + orderId).textContent = 'Rs ' + parseFloat(result.new_total).toFixed(2);
                    
                    // Reload the page to reflect all items changes for simplicity
                    window.location.reload();
                } else {
                    alert("Error: " + result.message);
                }
            } catch(e) {
                alert("Network error.");
                console.error(e);
            }
        }
    </script>
</body>
</html>
