<?php
// Enable error reporting for easier debugging of 500 errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/db.php';

$is_staff = isset($_SESSION['user_id']);
$is_customer = isset($_SESSION['customer_id']);

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<div class='ios-alert m-4' style='background: rgba(255,59,48,0.1); color: #CC2200;'>Invalid Customer ID.</div>");
}

$customer_id = (int)$_GET['id'];

// --- AUTHENTICATION ROUTING ---
if (!$is_staff) {
    // If not staff, MUST be the logged-in customer viewing their own profile
    if (!$is_customer || $_SESSION['customer_id'] != $customer_id) {
        header("Location: ../login.php");
        exit;
    }
} else {
    // Define hasRole helper specifically for the staff context
    function hasRole($allowed_roles) {
        if (!isset($_SESSION['user_role'])) return false;
        if (!is_array($allowed_roles)) $allowed_roles = [$allowed_roles];
        return in_array($_SESSION['user_role'], $allowed_roles);
    }
}
$isRep = $is_staff && hasRole('rep');

$message = '';

// --- AUTO DB MIGRATION FOR NEW CONTACT FIELDS ---
try {
    $pdo->exec("ALTER TABLE customers ADD COLUMN whatsapp VARCHAR(20) NULL");
    $pdo->exec("ALTER TABLE customers ADD COLUMN email VARCHAR(150) NULL");
} catch(PDOException $e) { /* Columns already exist */ }
// ------------------------------------------------

// --- HANDLE POST ACTIONS (Record Payment - STAFF ONLY) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'record_payment' && $is_staff) {
    $pay_amount = (float)$_POST['payment_amount'];
    $payment_method = $_POST['payment_method']; // 'Cash', 'Bank Transfer', 'Cheque'
    
    if ($pay_amount > 0) {
        try {
            $pdo->beginTransaction();

            // Distribute payment across unpaid orders, oldest first
            $stmt = $pdo->prepare("SELECT id, total_amount, paid_amount FROM orders WHERE customer_id = ? AND total_amount > paid_amount ORDER BY created_at ASC FOR UPDATE");
            $stmt->execute([$customer_id]);
            $unpaid_orders = $stmt->fetchAll();

            $remaining_payment = $pay_amount;
            $first_order_id = null; // Used to link the cheque if paying multiple orders

            foreach ($unpaid_orders as $order) {
                if ($remaining_payment <= 0) break;
                
                if (!$first_order_id) $first_order_id = $order['id'];

                $amount_due = $order['total_amount'] - $order['paid_amount'];
                $amount_to_apply = min($amount_due, $remaining_payment);

                $new_paid_amount = $order['paid_amount'] + $amount_to_apply;
                
                // Determine new status
                if ($payment_method === 'Cheque') {
                    $new_status = 'waiting';
                } else {
                    $new_status = ($new_paid_amount >= $order['total_amount']) ? 'paid' : 'pending';
                }

                $updateStmt = $pdo->prepare("UPDATE orders SET paid_amount = ?, payment_status = ? WHERE id = ?");
                $updateStmt->execute([$new_paid_amount, $new_status, $order['id']]);

                $remaining_payment -= $amount_to_apply;
            }

            // If it's a cheque, store details and link to the oldest processed invoice
            if ($payment_method === 'Cheque' && $first_order_id) {
                $bank_name = trim($_POST['cheque_bank']);
                $cheque_number = trim($_POST['cheque_number']);
                $banking_date = $_POST['cheque_date'];
                
                $chkStmt = $pdo->prepare("
                    INSERT INTO cheques (order_id, bank_name, cheque_number, banking_date, amount, status) 
                    VALUES (?, ?, ?, ?, ?, 'pending')
                    ON DUPLICATE KEY UPDATE bank_name=VALUES(bank_name), cheque_number=VALUES(cheque_number), banking_date=VALUES(banking_date), amount=amount+VALUES(amount)
                ");
                $chkStmt->execute([$first_order_id, $bank_name, $cheque_number, $banking_date, $pay_amount]);
            }

            $pdo->commit();
            $message = "<div class='ios-alert' style='background: rgba(52,199,89,0.1); color: #1A9A3A;'><i class='bi bi-check-circle-fill me-2'></i> Payment of Rs " . number_format($pay_amount, 2) . " recorded successfully via " . htmlspecialchars($payment_method) . "!</div>";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "<div class='ios-alert' style='background: rgba(255,59,48,0.1); color: #CC2200;'><i class='bi bi-exclamation-triangle-fill me-2'></i> Error recording payment: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        $message = "<div class='ios-alert' style='background: rgba(255,149,0,0.1); color: #C07000;'><i class='bi bi-info-circle-fill me-2'></i> Invalid amount.</div>";
    }
}

// --- HANDLE POST ACTIONS (Edit Profile) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_profile') {
    // Verify permissions: Must be staff OR the customer themselves
    if ($is_staff || ($is_customer && $_SESSION['customer_id'] == $customer_id)) {
        $name = trim($_POST['name']);
        $owner_name = trim($_POST['owner_name']);
        $phone = trim($_POST['phone']);
        $whatsapp = trim($_POST['whatsapp']);
        $email = trim($_POST['email']);
        $address = trim($_POST['address']);
        
        $email_ok = true;
        // Check email duplication if changed
        if (!empty($email)) {
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE email = ? AND id != ?");
            $checkStmt->execute([$email, $customer_id]);
            if ($checkStmt->fetchColumn() > 0) {
                $email_ok = false;
                $message = "<div class='ios-alert' style='background: rgba(255,149,0,0.1); color: #C07000;'><i class='bi bi-exclamation-triangle-fill me-2'></i> This email is already in use by another account.</div>";
            }
        }
        
        if ($email_ok && !empty($name)) {
            try {
                $stmt = $pdo->prepare("UPDATE customers SET name=?, owner_name=?, phone=?, whatsapp=?, email=?, address=? WHERE id=?");
                $stmt->execute([$name, $owner_name, $phone, $whatsapp, $email, $address, $customer_id]);
                
                // Update session name if the customer updated their own profile
                if ($is_customer && $_SESSION['customer_id'] == $customer_id) {
                    $_SESSION['customer_name'] = $name; 
                }
                
                $message = "<div class='ios-alert' style='background: rgba(52,199,89,0.1); color: #1A9A3A;'><i class='bi bi-check-circle-fill me-2'></i> Profile updated successfully!</div>";
            } catch (Exception $e) {
                $message = "<div class='ios-alert' style='background: rgba(255,59,48,0.1); color: #CC2200;'><i class='bi bi-exclamation-triangle-fill me-2'></i> Error updating profile: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        } elseif (empty($name)) {
            $message = "<div class='ios-alert' style='background: rgba(255,59,48,0.1); color: #CC2200;'><i class='bi bi-exclamation-triangle-fill me-2'></i> Business/Full Name is required.</div>";
        }
    }
}
// --------------------------------------------

// Fetch Customer Basic Info (Refetched here to ensure it catches newly updated data)
$stmt = $pdo->prepare("SELECT c.*, u.name as rep_name, r.name as route_name FROM customers c LEFT JOIN users u ON c.rep_id = u.id LEFT JOIN routes r ON c.route_id = r.id WHERE c.id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

if (!$customer) {
    die("<div class='ios-alert m-4' style='background: rgba(255,59,48,0.1); color: #CC2200;'>Customer not found.</div>");
}

// Safely default to empty string to prevent preg_replace null deprecation error in PHP 8.1+
$whatsapp_raw = $customer['whatsapp'] ?? '';
$whatsapp_clean = preg_replace('/[^0-9]/', '', $whatsapp_raw);
if (strlen($whatsapp_clean) == 10 && str_starts_with($whatsapp_clean, '0')) {
    $whatsapp_clean = '94' . substr($whatsapp_clean, 1); 
}

// Fetch Financial Metrics (Total Billed, Total Paid, Outstanding)
$metricsStmt = $pdo->prepare("
    SELECT 
        COUNT(id) as total_orders,
        SUM(total_amount) as total_billed,
        SUM(paid_amount) as total_paid,
        SUM(total_amount - paid_amount) as outstanding_balance
    FROM orders 
    WHERE customer_id = ?
");
$metricsStmt->execute([$customer_id]);
$metrics = $metricsStmt->fetch();

$outstanding_balance = $metrics['outstanding_balance'] ?: 0;

// Fetch Recent Orders (Limit 15)
$ordersStmt = $pdo->prepare("
    SELECT o.*, ch.status as cheque_status 
    FROM orders o 
    LEFT JOIN cheques ch ON o.id = ch.order_id
    WHERE o.customer_id = ? 
    ORDER BY o.created_at DESC LIMIT 15
");
$ordersStmt->execute([$customer_id]);
$orders = $ordersStmt->fetchAll();

// Fetch Linked Cheques
$chequesStmt = $pdo->prepare("
    SELECT ch.*, o.created_at as order_date 
    FROM cheques ch 
    JOIN orders o ON ch.order_id = o.id 
    WHERE o.customer_id = ? 
    ORDER BY ch.banking_date ASC
");
$chequesStmt->execute([$customer_id]);
$cheques = $chequesStmt->fetchAll();

// Generate Initials & Color for Avatar
$words = explode(" ", $customer['name']);
$initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
$colors = ['#FF2D55', '#007AFF', '#34C759', '#FF9500', '#AF52DE', '#30B0C7'];
$avatar_color = $colors[$customer['id'] % count($colors)];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Customer Profile - <?php echo htmlspecialchars($customer['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        /* --- Candent iOS Theme specific to Profile Page --- */
        :root {
            --ios-bg: #F2F2F7;
            --ios-surface: #FFFFFF;
            --ios-surface-2: #F2F2F7;
            --ios-separator: rgba(60,60,67,0.12);
            --ios-label: #000000;
            --ios-label-2: rgba(60,60,67,0.6);
            --ios-label-3: rgba(60,60,67,0.3);
            --accent: #30C88A;
            --accent-dark: #25A872;
            --radius-lg: 16px;
            --shadow-card: 0 2px 8px rgba(0,0,0,0.04), 0 0 1px rgba(0,0,0,0.02);
        }
        
        body { 
            background-color: var(--ios-bg); 
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Helvetica Neue', sans-serif; 
            color: var(--ios-label);
            -webkit-font-smoothing: antialiased;
        }

        .profile-wrapper { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 16px;
        }
        
        .dash-card {
            background: var(--ios-surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            border: 1px solid var(--ios-separator);
            overflow: hidden;
        }

        .contact-avatar-circle {
            width: 72px; height: 72px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 2rem;
            flex-shrink: 0; margin-right: 16px;
        }

        .quick-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 8px 14px; border-radius: 50px; font-size: 0.85rem; font-weight: 600;
            text-decoration: none; transition: all 0.15s ease; border: none; cursor: pointer; white-space: nowrap;
        }
        .quick-btn:active { transform: scale(0.97); }
        .quick-btn-primary { background: var(--accent); color: #fff; }
        .quick-btn-primary:hover { background: var(--accent-dark); color: #fff; }
        .quick-btn-secondary { background: var(--ios-surface); color: var(--ios-label); border: 1px solid var(--ios-separator); }
        .quick-btn-ghost { background: rgba(48,200,138,0.12); color: var(--accent-dark); }

        .metrics-card {
            border-radius: var(--radius-lg);
            padding: 16px 20px;
            color: #fff;
            display: flex; flex-direction: column; justify-content: center;
            height: 100%;
        }

        .table-ios-header th {
            background: var(--ios-surface-2) !important;
            color: var(--ios-label-2) !important;
            font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;
            border-bottom: 1px solid var(--ios-separator); padding: 12px 16px; position: sticky; top: 0;
        }
        .ios-table { width: 100%; border-collapse: collapse; }
        .ios-table td { padding: 12px 16px; border-bottom: 1px solid var(--ios-separator); vertical-align: middle; font-size: 0.9rem;}
        .ios-table tr:last-child td { border-bottom: none; }
        .ios-table tr:hover td { background: var(--ios-bg); }
        
        .ios-badge {
            font-size: 0.7rem; font-weight: 700; padding: 4px 10px; border-radius: 50px;
            display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;
        }
        .ios-badge.green { background: rgba(52,199,89,0.12); color: #1A9A3A; }
        .ios-badge.orange { background: rgba(255,149,0,0.15); color: #C07000; }
        .ios-badge.red { background: rgba(255,59,48,0.12); color: #CC2200; }
        .ios-badge.gray { background: rgba(60,60,67,0.1); color: var(--ios-label-2); }
        
        .ios-alert {
            padding: 12px 16px; border-radius: 12px; font-weight: 600; font-size: 0.9rem; margin-bottom: 20px; display: flex; align-items: center;
        }

        /* Modal Inputs */
        .modal-body .ios-input, .modal-body .form-select {
            background: #FFFFFF !important;
            border: 1px solid #C7C7CC !important;
            border-radius: 8px !important;
            padding: 10px 14px !important;
            font-size: 0.95rem !important;
            color: #000000 !important;
            width: 100%; outline: none; box-shadow: inset 0 1px 3px rgba(0,0,0,0.03) !important;
            transition: border 0.2s;
        }
        .modal-body .ios-input:focus, .modal-body .form-select:focus { 
            border-color: var(--accent) !important; box-shadow: 0 0 0 3px rgba(48,200,138,0.2) !important;
        }
        .modal-body .ios-label-sm { font-size: 0.75rem; font-weight: 600; color: var(--ios-label-2); margin-bottom: 6px; display: block; }
    </style>
</head>
<body>

<div class="profile-wrapper">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div id="backBtnContainer">
            <?php if(!$is_modal && $is_staff && !$isRep): ?>
                <a href="javascript:history.back()" class="quick-btn quick-btn-secondary" id="backBtn"><i class="bi bi-arrow-left"></i> Back</a>
            <?php elseif(!$is_modal && $isRep): ?>
                <a href="javascript:history.back()" class="quick-btn quick-btn-primary" id="backBtn"><i class="bi bi-arrow-left"></i> Done</a>
            <?php elseif(!$is_modal && $is_customer): ?>
                <a href="../index.php" class="quick-btn quick-btn-primary" id="backBtn"><i class="bi bi-shop"></i> Back to Store</a>
            <?php endif; ?>
        </div>
    </div>

    <?php echo $message; ?>

    <!-- Customer Header & Location Map -->
    <div class="row g-3 mb-3">
        <!-- Profile Info -->
        <div class="col-md-7">
            <div class="dash-card h-100 p-4 position-relative d-flex flex-column justify-content-center">
                <?php if($is_staff || ($is_customer && $_SESSION['customer_id'] == $customer['id'])): ?>
                    <button class="quick-btn quick-btn-secondary position-absolute top-0 end-0 m-3" data-bs-toggle="modal" data-bs-target="#editProfileModal" style="padding: 6px 12px; font-size: 0.75rem;">
                        <i class="bi bi-pencil-square"></i> Edit
                    </button>
                <?php endif; ?>
                
                <div class="d-flex align-items-center mb-3">
                    <div class="contact-avatar-circle" style="background: <?php echo $avatar_color; ?>20; color: <?php echo $avatar_color; ?>;">
                        <?php echo $initials; ?>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1" style="font-size: 1.4rem; color: var(--ios-label);"><?php echo htmlspecialchars($customer['name']); ?></h3>
                        <div style="font-size: 0.85rem; color: var(--ios-label-2);"><i class="bi bi-person-badge me-1"></i> Owner: <?php echo htmlspecialchars($customer['owner_name'] ?: 'N/A'); ?></div>
                    </div>
                </div>
                
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php if($customer['phone']): ?>
                        <a href="tel:<?php echo htmlspecialchars($customer['phone']); ?>" class="quick-btn quick-btn-secondary" style="font-size: 0.75rem;"><i class="bi bi-telephone-fill" style="color: #007AFF;"></i> <?php echo htmlspecialchars($customer['phone']); ?></a>
                    <?php endif; ?>
                    <?php if($customer['whatsapp']): ?>
                        <a href="https://wa.me/<?php echo $whatsapp_clean; ?>" target="_blank" class="quick-btn quick-btn-secondary" style="font-size: 0.75rem;"><i class="bi bi-whatsapp" style="color: #34C759;"></i> WhatsApp</a>
                    <?php endif; ?>
                    <?php if($customer['email']): ?>
                        <a href="mailto:<?php echo htmlspecialchars($customer['email']); ?>" class="quick-btn quick-btn-secondary" style="font-size: 0.75rem;"><i class="bi bi-envelope-fill" style="color: #FF3B30;"></i> Email</a>
                    <?php endif; ?>
                </div>

                <div style="font-size: 0.85rem; color: var(--ios-label-2); margin-bottom: 12px;">
                    <i class="bi bi-geo-alt-fill me-1"></i> <?php echo nl2br(htmlspecialchars($customer['address'] ?: 'No address recorded.')); ?>
                </div>
                
                <div class="d-flex gap-2 flex-wrap mt-auto">
                    <span class="ios-badge gray"><i class="bi bi-person"></i> Rep: <?php echo htmlspecialchars($customer['rep_name'] ?: 'Admin'); ?></span>
                    <?php if($is_staff): ?>
                        <span class="ios-badge gray outline"><i class="bi bi-signpost-split"></i> Route: <?php echo htmlspecialchars($customer['route_name'] ?: 'None'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Map Column -->
        <div class="col-md-5">
            <div class="dash-card h-100 p-2 d-flex flex-column">
                <?php if($customer['latitude'] && $customer['longitude']): ?>
                    <div class="d-flex justify-content-between align-items-center px-2 pt-2 mb-2">
                        <span style="font-size: 0.75rem; font-weight: 700; color: var(--ios-label-2); text-transform: uppercase;"><i class="bi bi-map-fill text-danger me-1"></i> Location</span>
                        <a href="https://maps.google.com/?q=<?php echo $customer['latitude']; ?>,<?php echo $customer['longitude']; ?>" target="_blank" class="text-decoration-none fw-bold" style="font-size: 0.75rem; color: #007AFF;"><i class="bi bi-box-arrow-up-right"></i> Open Map</a>
                    </div>
                    <div class="rounded overflow-hidden" style="flex: 1; min-height: 180px; background: var(--ios-surface-2);">
                        <iframe src="https://maps.google.com/maps?q=<?php echo $customer['latitude']; ?>,<?php echo $customer['longitude']; ?>&z=15&output=embed" frameborder="0" style="width: 100%; height: 100%; border:0;" allowfullscreen></iframe>
                    </div>
                <?php else: ?>
                    <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center p-4" style="background: var(--ios-surface-2); border-radius: 12px;">
                        <i class="bi bi-geo-slash" style="font-size: 2.5rem; color: var(--ios-label-4); margin-bottom: 10px;"></i>
                        <span style="font-weight: 600; font-size: 0.9rem; color: var(--ios-label-2);">No Location</span>
                        <small style="color: var(--ios-label-3); margin-top: 4px;">Update coordinates to view map.</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Financial Summary Row -->
    <div class="row mb-4 g-3">
        <div class="col-md-3 col-6">
            <div class="metrics-card" style="background: linear-gradient(145deg, #5856D6, #4543B0);">
                <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: rgba(255,255,255,0.7); margin-bottom: 2px;">Total Orders</div>
                <h3 class="mb-0 fw-bold" style="font-size: 1.8rem;"><?php echo $metrics['total_orders'] ?: 0; ?></h3>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="metrics-card" style="background: linear-gradient(145deg, #30B0C7, #1A95AC);">
                <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: rgba(255,255,255,0.7); margin-bottom: 2px;">Total Billed</div>
                <h4 class="mb-0 fw-bold" style="font-size: 1.4rem;">Rs <?php echo number_format($metrics['total_billed'] ?: 0, 2); ?></h4>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="metrics-card" style="background: linear-gradient(145deg, #34C759, #30D158);">
                <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: rgba(255,255,255,0.7); margin-bottom: 2px;">Total Paid</div>
                <h4 class="mb-0 fw-bold" style="font-size: 1.4rem;">Rs <?php echo number_format($metrics['total_paid'] ?: 0, 2); ?></h4>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="metrics-card" style="background: linear-gradient(145deg, #FF3B30, #CC1500);">
                <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: rgba(255,255,255,0.8); margin-bottom: 2px;">Outstanding</div>
                <h3 class="mb-2 fw-bold" style="font-size: 1.5rem;">Rs <?php echo number_format($outstanding_balance, 2); ?></h3>
                
                <?php if($outstanding_balance > 0): ?>
                    <?php if($is_staff): ?>
                        <button class="quick-btn w-100" style="background: rgba(255,255,255,0.25); color: #fff; font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                            <i class="bi bi-cash-coin"></i> Record Pay
                        </button>
                    <?php else: ?>
                        <a href="mailto:admin@fintrix.com?subject=Payment For Outstanding Account" class="quick-btn w-100 text-decoration-none" style="background: rgba(255,255,255,0.25); color: #fff; font-size: 0.75rem;">
                            <i class="bi bi-envelope"></i> Contact Us
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <div style="font-size: 0.7rem; color: rgba(255,255,255,0.6);"><i class="bi bi-check-circle-fill me-1"></i>All clear</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Left Column: Recent Orders -->
        <div class="col-lg-7 mb-4">
            <div class="dash-card h-100 overflow-hidden d-flex flex-column">
                <div class="p-3 border-bottom" style="background: var(--ios-surface);">
                    <h6 class="fw-bold m-0" style="font-size: 0.95rem; color: var(--ios-label);"><i class="bi bi-receipt me-2 text-primary"></i>Recent Orders</h6>
                </div>
                <div class="table-responsive" style="flex: 1; max-height: 350px;">
                    <table class="ios-table text-center" style="margin: 0;">
                        <thead style="position: sticky; top: 0; z-index: 5;">
                            <tr class="table-ios-header">
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $o): ?>
                            <tr>
                                <td>
                                    <a href="view_invoice.php?id=<?php echo $o['id']; ?>" class="fw-bold text-decoration-none" style="color: #0055CC;" target="_blank">#<?php echo str_pad($o['id'], 6, '0', STR_PAD_LEFT); ?></a>
                                </td>
                                <td><span style="font-size: 0.85rem; color: var(--ios-label-2); font-weight: 500;"><?php echo date('M d, Y', strtotime($o['created_at'])); ?></span></td>
                                <td class="fw-bold" style="color: var(--ios-label);">Rs <?php echo number_format($o['total_amount'], 2); ?></td>
                                <td>
                                    <div class="mb-1"><span class="ios-badge gray outline px-2" style="font-size: 0.6rem;"><?php echo htmlspecialchars($o['payment_method']); ?></span></div>
                                    <?php if($o['payment_status'] == 'paid'): ?>
                                        <span class="ios-badge green">Paid</span>
                                    <?php elseif($o['payment_status'] == 'waiting'): ?>
                                        <span class="ios-badge blue">Waiting (Chq)</span>
                                    <?php else: ?>
                                        <span class="ios-badge orange">Pending</span>
                                        <?php if($o['payment_method'] === 'Cheque' && $o['cheque_status']): ?>
                                            <div style="font-size: 0.65rem; color: var(--ios-label-3); margin-top: 2px;">(Chq: <?php echo htmlspecialchars($o['cheque_status']); ?>)</div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($orders)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted fw-bold">No orders found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Cheques Management -->
        <div class="col-lg-5 mb-4">
            <div class="dash-card h-100 overflow-hidden d-flex flex-column">
                <div class="p-3 border-bottom" style="background: var(--ios-surface);">
                    <h6 class="fw-bold m-0" style="font-size: 0.95rem; color: var(--ios-label);"><i class="bi bi-credit-card-2-front me-2 text-warning"></i>Linked Cheques</h6>
                </div>
                <div class="table-responsive" style="flex: 1; max-height: 350px;">
                    <table class="ios-table text-center" style="margin: 0;">
                        <thead style="position: sticky; top: 0; z-index: 5;">
                            <tr class="table-ios-header">
                                <th class="text-start">Bank & Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($cheques as $ch): ?>
                            <tr>
                                <td class="text-start">
                                    <div style="font-weight: 700; font-size: 0.85rem; color: var(--ios-label);"><?php echo htmlspecialchars($ch['bank_name']); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--ios-label-3);">No: <?php echo htmlspecialchars($ch['cheque_number']); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--ios-label-2); font-weight: 600; margin-top: 2px;"><i class="bi bi-calendar text-primary me-1"></i> <?php echo date('d M Y', strtotime($ch['banking_date'])); ?></div>
                                </td>
                                <td class="fw-bold text-dark">Rs <?php echo number_format($ch['amount'], 2); ?></td>
                                <td>
                                    <?php 
                                        if($ch['status'] === 'passed') echo '<span class="ios-badge green">Passed</span>';
                                        elseif($ch['status'] === 'returned') echo '<span class="ios-badge red">Returned</span>';
                                        else echo '<span class="ios-badge orange">Pending</span>';
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($cheques)): ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted fw-bold">No cheques linked.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--ios-bg);">
            <form method="POST" action="">
                <div class="modal-header" style="background: var(--ios-surface);">
                    <h5 class="modal-title fw-bold" style="font-size: 1.1rem;"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_profile">
                    
                    <div class="mb-3">
                        <label class="ios-label-sm">Business/Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="ios-input fw-bold" value="<?php echo htmlspecialchars($customer['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="ios-label-sm">Owner/Contact Name</label>
                        <input type="text" name="owner_name" class="ios-input" value="<?php echo htmlspecialchars($customer['owner_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="ios-label-sm">Phone Number</label>
                            <input type="tel" name="phone" class="ios-input" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
                        </div>
                        <div class="col-6">
                            <label class="ios-label-sm" style="color: #1A9A3A;">WhatsApp</label>
                            <input type="tel" name="whatsapp" class="ios-input" value="<?php echo htmlspecialchars($customer['whatsapp'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="ios-label-sm">Email Address</label>
                        <input type="email" name="email" class="ios-input" value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>">
                        <small style="font-size: 0.7rem; color: var(--ios-label-3); margin-top: 4px; display: block;">Used for E-commerce login and digital receipts.</small>
                    </div>
                    
                    <div class="mb-2">
                        <label class="ios-label-sm">Full Address</label>
                        <textarea name="address" class="ios-input" rows="2"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background: var(--ios-surface);">
                    <button type="button" class="quick-btn quick-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="quick-btn quick-btn-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<?php if($outstanding_balance > 0 && $is_staff): ?>
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--ios-bg);">
            <form method="POST" action="">
                <div class="modal-header" style="background: var(--ios-surface);">
                    <h5 class="modal-title fw-bold" style="font-size: 1.1rem;"><i class="bi bi-cash-coin text-success me-2"></i>Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="record_payment">
                    
                    <div class="ios-alert text-center mb-4" style="background: rgba(0,122,255,0.1); color: #0055CC; display: block;">
                        <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Outstanding Balance</div>
                        <div style="font-size: 1.4rem; font-weight: 800;">Rs <?php echo number_format($outstanding_balance, 2); ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="ios-label-sm">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" id="payMethodSelect" class="form-select fw-bold" required>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="ios-label-sm">Payment Received (Rs) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="payment_amount" id="payAmountInput" class="ios-input fw-bold" style="color: #1A9A3A; font-size: 1.2rem; height: 50px;" max="<?php echo $outstanding_balance; ?>" required placeholder="0.00">
                        <small style="font-size: 0.7rem; color: var(--ios-label-3); margin-top: 6px; display: block;"><i class="bi bi-info-circle-fill me-1"></i> Automatically clears the oldest pending invoices first.</small>
                    </div>

                    <!-- Hidden Cheque Fields -->
                    <div id="chequeFields" style="display: none; background: rgba(255,149,0,0.08); border-radius: 12px; padding: 16px; border: 1px solid rgba(255,149,0,0.2);">
                        <h6 class="fw-bold mb-3 pb-2" style="font-size: 0.9rem; color: #C07000; border-bottom: 1px solid rgba(255,149,0,0.2);"><i class="bi bi-credit-card-2-front me-2"></i>Cheque Details</h6>
                        <div class="mb-3">
                            <label class="ios-label-sm" style="color: #C07000;">Bank Name <span class="text-danger">*</span></label>
                            <input type="text" name="cheque_bank" id="chkBank" class="ios-input">
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="ios-label-sm" style="color: #C07000;">Cheque No. <span class="text-danger">*</span></label>
                                <input type="text" name="cheque_number" id="chkNum" class="ios-input">
                            </div>
                            <div class="col-6">
                                <label class="ios-label-sm" style="color: #C07000;">Banking Date <span class="text-danger">*</span></label>
                                <input type="date" name="cheque_date" id="chkDate" class="ios-input">
                            </div>
                        </div>
                        <small style="font-size: 0.7rem; color: #C07000; margin-top: 8px; display: block; opacity: 0.9;">Note: Invoices covered will remain in 'Waiting' until passed.</small>
                    </div>

                </div>
                <div class="modal-footer" style="background: var(--ios-surface);">
                    <button type="button" class="quick-btn quick-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="quick-btn" style="background: #CC2200; color: #fff; padding: 10px 20px;">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Payment Form Toggler & Iframe UX JS -->
<script>
    // Hide Back Button if opened inside an iframe (popup modal)
    if (window.self !== window.top) {
        const backBtnContainer = document.getElementById('backBtnContainer');
        if (backBtnContainer) {
            backBtnContainer.style.display = 'none';
        }
    }

    // Cheque Toggle Logic
    const methodSelect = document.getElementById('payMethodSelect');
    const chequeFields = document.getElementById('chequeFields');
    const chkBank = document.getElementById('chkBank');
    const chkNum = document.getElementById('chkNum');
    const chkDate = document.getElementById('chkDate');

    if (methodSelect) {
        methodSelect.addEventListener('change', function() {
            if (this.value === 'Cheque') {
                chequeFields.style.display = 'block';
                chkBank.required = true;
                chkNum.required = true;
                chkDate.required = true;
            } else {
                chequeFields.style.display = 'none';
                chkBank.required = false;
                chkNum.required = false;
                chkDate.required = false;
            }
        });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>