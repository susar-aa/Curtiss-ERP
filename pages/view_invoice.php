<?php
session_start(); // Only used for UI logic, not for redirection.
require_once '../config/db.php';

// Check if user is an authenticated staff member
$is_staff = isset($_SESSION['user_id']);

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<div style='padding:20px; font-family:sans-serif;'>Invalid Order ID.</div>");
}

$order_id = (int)$_GET['id'];

// Fetch Order, Customer, and Rep Details
$stmt = $pdo->prepare("
    SELECT o.*, c.name as customer_name, c.address, c.phone, u.name as rep_name 
    FROM orders o 
    LEFT JOIN customers c ON o.customer_id = c.id 
    LEFT JOIN users u ON o.rep_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die("<div style='padding:20px; font-family:sans-serif;'>Order not found or has been deleted.</div>");
}

// Fetch Order Items
$itemStmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.sku 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$itemStmt->execute([$order_id]);
$items = $itemStmt->fetchAll();

// Construct Dynamic File Name (Customer Name - Date - Payment Method.pdf)
$custNameStr = !empty($order['customer_name']) ? $order['customer_name'] : 'Walk-in';
$dateStr = date('Y-m-d', strtotime($order['created_at']));
$payStr = $order['payment_method'];

$rawFileName = "{$custNameStr} - {$dateStr} - {$payStr}";
// Clean filename of any potentially invalid OS characters
$cleanFileName = preg_replace('/[^A-Za-z0-9 \-_]/', '', $rawFileName) . '.pdf';

// Calculate Balance
$paidAmount = isset($order['paid_amount']) ? $order['paid_amount'] : 0;
$balance = $order['total_amount'] - $paidAmount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?> - Candent</title>
    
    <!-- Modern Fonts & Bootstrap -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <style>
        :root {
            --brand-primary: #007AFF;
            --brand-success: #30C88A;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --bg-light: #f8fafc;
        }

        body { 
            background-color: <?php echo $is_staff ? '#f1f5f9' : '#ffffff'; ?>; 
            font-family: 'Inter', system-ui, -apple-system, sans-serif; 
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }
        
        .invoice-wrapper { max-width: 850px; margin: <?php echo $is_staff ? '2.5rem auto' : '0 auto'; ?>; }
        
        .action-bar { 
            background: #ffffff; padding: 1rem 1.5rem; border-radius: 12px; 
            border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); 
            margin-bottom: 24px; 
        }
        
        #invoice-content { 
            background: #ffffff; 
            padding: <?php echo $is_staff ? '3rem 3.5rem' : '1.5rem'; ?>; 
            border-radius: 16px; 
            box-shadow: <?php echo $is_staff ? '0 20px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.01)' : 'none'; ?>; 
            border: <?php echo $is_staff ? '1px solid var(--border-color)' : 'none'; ?>;
            position: relative; overflow: hidden;
        }
        
        /* Premium Top Accent Line */
        .invoice-top-accent { 
            position: absolute; top: 0; left: 0; right: 0; height: 6px; 
            background: linear-gradient(90deg, var(--brand-success), var(--brand-primary)); 
        }

        .company-details { font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; }
        
        .invoice-title { font-size: 2.25rem; font-weight: 800; color: var(--text-main); letter-spacing: -1px; margin-bottom: 0.25rem; line-height: 1;}
        .invoice-number { font-size: 1.1rem; font-weight: 600; color: var(--text-muted); }

        .section-title { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: var(--text-muted); margin-bottom: 0.75rem; }
        
        /* Info Boxes */
        .info-box { background-color: var(--bg-light); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.25rem; height: 100%; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.875rem; align-items: center;}
        .info-row:last-child { margin-bottom: 0; }
        .info-label { color: var(--text-muted); font-weight: 500; }
        .info-value { color: var(--text-main); font-weight: 600; text-align: right; }
        
        /* Table Styling */
        .table-invoice { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 2rem; margin-bottom: 2rem; }
        .table-invoice th { 
            background-color: var(--bg-light); color: var(--text-muted); 
            font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; 
            padding: 12px 16px; border-bottom: 1px solid var(--border-color); border-top: 1px solid var(--border-color); 
        }
        .table-invoice th:first-child { border-top-left-radius: 8px; border-left: 1px solid var(--border-color); }
        .table-invoice th:last-child { border-top-right-radius: 8px; border-right: 1px solid var(--border-color); }
        
        .table-invoice td { padding: 16px; border-bottom: 1px solid var(--border-color); vertical-align: middle; color: var(--text-main); font-size: 0.875rem; }
        .table-invoice td:first-child { border-left: 1px solid var(--border-color); }
        .table-invoice td:last-child { border-right: 1px solid var(--border-color); }
        .table-invoice tr:last-child td:first-child { border-bottom-left-radius: 8px; }
        .table-invoice tr:last-child td:last-child { border-bottom-right-radius: 8px; }
        
        .item-name { font-weight: 600; color: var(--text-main); display: block; }
        .item-sku { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem; }

        /* Totals Section */
        .totals-wrapper { width: 100%; max-width: 340px; margin-left: auto; }
        .totals-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 0.875rem; }
        .totals-label { color: var(--text-muted); font-weight: 500; }
        .totals-value { color: var(--text-main); font-weight: 600; }
        
        .grand-total-box { 
            background-color: var(--bg-light); border: 1px solid var(--border-color); 
            border-radius: 10px; padding: 1rem 1.25rem; margin-top: 0.5rem; margin-bottom: 0.5rem;
        }
        .grand-total-row { display: flex; justify-content: space-between; align-items: center; }
        .grand-total-label { color: var(--text-main); font-weight: 700; font-size: 1rem; }
        .grand-total-value { color: var(--brand-primary); font-weight: 800; font-size: 1.25rem; }

        .balance-row { display: flex; justify-content: space-between; padding: 8px 1.25rem; font-size: 0.95rem; background: #fff; border: 1px solid var(--border-color); border-radius: 8px;}
        .balance-label { font-weight: 700; color: var(--text-main); }
        .balance-value { font-weight: 800; }

        /* Badges */
        .status-badge { padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block; }
        .status-paid { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .status-pending { background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .status-partial { background-color: #eff6ff; color: #1e3a8a; border: 1px solid #bfdbfe; }

        .terms-box { font-size: 0.8rem; color: var(--text-muted); line-height: 1.6; }
        
        .signature-line { border-top: 1px solid var(--border-color); width: 180px; margin-top: 4rem; padding-top: 0.5rem; text-align: center; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); }

        @media print {
            body { background-color: #fff; margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .invoice-wrapper { margin: 0; max-width: 100%; }
            #invoice-content { box-shadow: none; padding: 0; border: none; }
            .no-print { display: none !important; }
            .invoice-top-accent { display: none; }
        }
    </style>
</head>
<body>

<div class="invoice-wrapper">
    
    <?php if($is_staff): ?>
    <div class="action-bar d-flex justify-content-between align-items-center no-print flex-wrap gap-2">
        <a href="javascript:window.close();" class="btn btn-light border fw-semibold text-secondary">
            <i class="bi bi-x-lg me-1"></i> Close
        </a>
        <div class="d-flex gap-2">
            <button onclick="shareInvoice()" class="btn btn-outline-primary fw-semibold" id="shareBtn">
                <i class="bi bi-share me-1"></i> Share
            </button>
            <button onclick="downloadInvoice()" class="btn btn-outline-danger fw-semibold" id="downloadBtn">
                <i class="bi bi-file-earmark-pdf me-1"></i> PDF
            </button>
            <button onclick="window.print()" class="btn btn-primary fw-semibold px-4 shadow-sm">
                <i class="bi bi-printer me-1"></i> Print Invoice
            </button>
        </div>
    </div>
    <?php else: ?>
    <!-- Public UI Actions (Mobile Friendly Download) -->
    <div class="p-3 text-center bg-white border-bottom mb-4 no-print shadow-sm">
        <button onclick="downloadInvoice()" class="btn btn-primary rounded-pill fw-semibold px-4 py-2" id="downloadBtn">
            <i class="bi bi-download me-2"></i> Save Official Receipt
        </button>
    </div>
    <?php endif; ?>

    <div id="invoice-content">
        <!-- Top Gradient Line -->
        <div class="invoice-top-accent"></div>
        
        <!-- Header Section -->
        <div class="row mb-5 align-items-start">
            <div class="col-sm-6 mb-4 mb-sm-0">
                <img src="/images/logo/croped-white-logo.png" alt="Candent" style="height: 65px; display: block; object-fit: contain;" onerror="this.onerror=null; this.src='https://via.placeholder.com/200x65/ffffff/000000?text=CANDENT';">
                <div class="company-details mt-3">
                    79, Dambakanda Estate, Boyagane,<br>
                    Kurunegala, Sri Lanka.<br>
                    Tel: 076 140 7876<br>
                    candentlk@gmail.com
                </div>
            </div>
            <div class="col-sm-6 text-sm-end">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number mb-3">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></div>
                
                <?php 
                    $statusClass = 'status-pending';
                    $statusText = strtoupper($order['payment_status']);
                    if($order['payment_status'] == 'paid') {
                        $statusClass = 'status-paid';
                    } elseif ($paidAmount > 0 && $balance > 0) {
                        $statusClass = 'status-partial';
                        $statusText = 'PARTIAL';
                    }
                ?>
                <div class="status-badge <?php echo $statusClass; ?>">
                    <?php echo $statusText; ?>
                </div>
            </div>
        </div>

        <!-- Billing & Details Grid -->
        <div class="row g-4 mb-2">
            <!-- Billed To -->
            <div class="col-md-7">
                <div class="section-title">Billed To</div>
                <?php if(!empty($order['customer_name'])): ?>
                    <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($order['customer_name']); ?></h5>
                    <div class="text-muted small" style="line-height: 1.5;">
                        <?php echo nl2br(htmlspecialchars($order['address'])); ?><br>
                        <?php if(!empty($order['phone'])): ?>
                            <i class="bi bi-telephone text-secondary me-1"></i> <?php echo htmlspecialchars($order['phone']); ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <h5 class="fw-bold text-muted fst-italic mb-0">Walk-in Customer</h5>
                <?php endif; ?>
            </div>
            
            <!-- Invoice Info Box -->
            <div class="col-md-5">
                <div class="info-box shadow-sm">
                    <div class="info-row">
                        <span class="info-label">Invoice Date</span>
                        <span class="info-value"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment Terms</span>
                        <span class="info-value text-capitalize"><?php echo htmlspecialchars($order['payment_method']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Sales Rep</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['rep_name'] ?: 'System Admin'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table-invoice">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">#</th>
                    <th style="width: 45%;">Item Description</th>
                    <th style="width: 10%; text-align: center;">Qty</th>
                    <th style="width: 15%; text-align: right;">Rate</th>
                    <th style="width: 10%; text-align: right;">Dis.</th>
                    <th style="width: 15%; text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 1;
                foreach($items as $item): 
                    $grossTotal = $item['quantity'] * $item['price'];
                    $netTotal = $grossTotal - $item['discount'];
                ?>
                <tr>
                    <td style="text-align: center; color: var(--text-muted); font-weight: 500;"><?php echo str_pad($counter++, 2, '0', STR_PAD_LEFT); ?></td>
                    <td>
                        <span class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></span>
                        <?php if(!empty($item['sku'])): ?>
                            <div class="item-sku">SKU: <?php echo htmlspecialchars($item['sku']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center; font-weight: 600;"><?php echo $item['quantity']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($item['price'], 2); ?></td>
                    <td style="text-align: right; color: <?php echo $item['discount'] > 0 ? '#ef4444' : 'var(--text-muted)'; ?>;">
                        <?php echo $item['discount'] > 0 ? number_format($item['discount'], 2) : '-'; ?>
                    </td>
                    <td style="text-align: right; font-weight: 600; color: var(--text-main);"><?php echo number_format($netTotal, 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Footer / Totals Section -->
        <div class="row mt-4">
            <!-- Notes & Terms -->
            <div class="col-md-6 order-2 order-md-1 mt-4 mt-md-0">
                <div class="section-title">Terms & Conditions</div>
                <div class="terms-box">
                    Goods once sold will not be taken back unless defective.<br>
                    Please ensure all details are correct before leaving the premises.<br>
                    For bank transfers, please use Invoice # as the reference.
                </div>
                
                <!-- Signature Block -->
                <div class="signature-line">
                    Authorized Signature
                </div>
            </div>
            
            <!-- Totals Calculation -->
            <div class="col-md-6 order-1 order-md-2">
                <div class="totals-wrapper">
                    <div class="totals-row">
                        <span class="totals-label">Sub Total</span>
                        <span class="totals-value">Rs <?php echo number_format($order['subtotal'], 2); ?></span>
                    </div>
                    
                    <?php if($order['discount_amount'] > 0): ?>
                    <div class="totals-row text-danger">
                        <span class="totals-label text-danger">Bill Discount</span>
                        <span class="totals-value fw-bold">- Rs <?php echo number_format($order['discount_amount'], 2); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(isset($order['tax_amount']) && $order['tax_amount'] > 0): ?>
                    <div class="totals-row">
                        <span class="totals-label">VAT / Tax</span>
                        <span class="totals-value">+ Rs <?php echo number_format($order['tax_amount'], 2); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Net Amount Box -->
                    <div class="grand-total-box shadow-sm">
                        <div class="grand-total-row">
                            <span class="grand-total-label">Net Bill Amount</span>
                            <span class="grand-total-value">Rs <?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>

                    <!-- Payments & Balance -->
                    <div class="totals-row mt-3">
                        <span class="totals-label">Amount Paid</span>
                        <span class="totals-value text-success">Rs <?php echo number_format($paidAmount, 2); ?></span>
                    </div>
                    
                    <div class="balance-row mt-2 shadow-sm">
                        <span class="balance-label <?php echo $balance <= 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo $balance < 0 ? 'Change Due' : 'Balance Due'; ?>
                        </span>
                        <span class="balance-value <?php echo $balance <= 0 ? 'text-success' : 'text-danger'; ?>">
                            Rs <?php echo number_format(abs($balance), 2); ?>
                        </span>
                    </div>

                </div>
            </div>
        </div>

        <div class="row mt-5 pt-3 text-center no-print border-top">
            <div class="col-12 text-muted" style="font-size: 0.75rem;">
                Powered by <a href="https://suzxlabs.com" target="_blank" class="text-decoration-none fw-bold text-secondary">Suzxlabs</a>
            </div>
        </div>

    </div>
</div>

<script>
    const pdfOptions = {
        margin:       [0.4, 0, 0.4, 0], // Top, Left, Bottom, Right
        filename:     '<?php echo addslashes($cleanFileName); ?>',
        image:        { type: 'jpeg', quality: 1 },
        html2canvas:  { scale: 2, useCORS: true, logging: false, windowWidth: 850 },
        jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    function downloadInvoice() {
        const element = document.getElementById('invoice-content');
        const btn = document.getElementById('downloadBtn');
        const originalHtml = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generating...';

        html2pdf().set(pdfOptions).from(element).save().then(() => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    }

    async function shareInvoice() {
        const element = document.getElementById('invoice-content');
        const btn = document.getElementById('shareBtn');
        if (!btn) return;
        const originalHtml = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Preparing...';

        try {
            const pdfBlob = await html2pdf().set(pdfOptions).from(element).output('blob');
            const file = new File([pdfBlob], '<?php echo addslashes($cleanFileName); ?>', { type: 'application/pdf' });
            
            if (navigator.share && navigator.canShare && navigator.canShare({ files: [file] })) {
                await navigator.share({
                    files: [file],
                    title: 'Invoice #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>',
                    text: 'Please find your invoice attached.'
                });
            } else {
                alert('Native file sharing is not fully supported on this browser. The invoice will be downloaded instead.');
                downloadInvoice();
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                alert('An error occurred while trying to share. The invoice will be downloaded instead.');
                downloadInvoice();
            }
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }
</script>

</body>
</html>