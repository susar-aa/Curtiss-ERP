<?php
// Script to generate app/Views/grns/view.php based on invoice_view.php style

$css = file_get_contents('scratch_style.txt');

$grnView = <<<PHP
<?php
// Calculate Totals
\$grandTotal = 0;
foreach (\$data['items'] as \$item) {
    \$grandTotal += floatval(\$item->quantity) * floatval(\$item->unit_cost);
}

// Fetch Pricing Data
\$db = new Database();
foreach (\$data['items'] as \$grnItem) {
    \$grnItem->retail_price = 0;
    \$grnItem->wholesale_price = 0;
    if (!empty(\$grnItem->item_id)) {
        \$db->query("SELECT price, wholesale_price FROM items WHERE id = :id");
        \$db->bind(':id', \$grnItem->item_id);
        \$itemPrices = \$db->single();
        if (\$itemPrices) {
            \$grnItem->retail_price = floatval(\$itemPrices->price ?? 0);
            \$grnItem->wholesale_price = floatval(\$itemPrices->wholesale_price ?? 0);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goods Receipt Note <?= htmlspecialchars(\$data['grn']->grn_number) ?> - <?= APP_NAME ?></title>
    <!-- Modern Typography & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
$css
</head>
<body>

    <!-- Top Action Controls -->
    <div class="controls-container">
        <div class="controls-title">
            <i class="ph ph-file-text" style="font-size: 20px;"></i>
            GOODS RECEIPT NOTE
            <?php if(\$data['grn']->is_approved): ?>
            <span class="badge-live"><i class="ph-fill ph-check-circle"></i> APPROVED</span>
            <?php else: ?>
            <span class="status-badge status-pending">PENDING</span>
            <?php endif; ?>
        </div>
        <div class="print-controls">
            <a href="<?= APP_URL ?>/grn" class="btn-action" style="background:#F1F5F9; color:#0F172A; border-color:#CBD5E1;">
                <i class="ph ph-arrow-left"></i> Back
            </a>
            <button onclick="window.print()" class="btn-action">
                <i class="ph ph-printer"></i> Print Document
            </button>
        </div>
    </div>

    <!-- Main Document Wrapper -->
    <div class="page-wrapper">
        <?php if(\$data['grn']->is_approved): ?>
        <div class="stamp-paid">
            <div class="stamp-inner">APPROVED</div>
        </div>
        <?php endif; ?>

        <div class="main-content">
            <!-- Header Section -->
            <div class="invoice-header">
                <div class="company-info">
                    <?php if(!empty(\$data['company']->logo_path)): ?>
                        <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars(\$data['company']->logo_path) ?>" alt="Company Logo" class="company-logo">
                    <?php else: ?>
                        <div class="company-name"><?= htmlspecialchars(\$data['company']->company_name) ?></div>
                    <?php endif; ?>
                    <div class="company-details">
                        <?php if(!empty(\$data['company']->address)): ?>
                            <?= nl2br(htmlspecialchars(\$data['company']->address)) ?><br>
                        <?php endif; ?>
                        <?php if(!empty(\$data['company']->phone)): ?>
                            <?= htmlspecialchars(\$data['company']->phone) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="invoice-meta">
                    <div class="document-title">GOODS RECEIPT</div>
                    <table class="meta-table">
                        <tr>
                            <th>GRN #</th>
                            <td><?= htmlspecialchars(\$data['grn']->grn_number) ?></td>
                        </tr>
                        <tr>
                            <th>Received Date</th>
                            <td><?= date('F j, Y', strtotime(\$data['grn']->grn_date)) ?></td>
                        </tr>
                        <?php if(!empty(\$data['grn']->receipt_number)): ?>
                        <tr>
                            <th>Supplier Invoice</th>
                            <td><?= htmlspecialchars(\$data['grn']->receipt_number) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if(!empty(\$data['grn']->po_number)): ?>
                        <tr>
                            <th>PO Reference</th>
                            <td><?= htmlspecialchars(\$data['grn']->po_number) ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <!-- Parties Section -->
            <div class="customer-section">
                <div class="info-card">
                    <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 6px;">Supplier (Received From)</div>
                    <div class="customer-name"><?= htmlspecialchars(\$data['grn']->vendor_name) ?></div>
                    <div class="customer-details">
                        <?php if(!empty(\$data['grn']->address)): ?>
                            <?= nl2br(htmlspecialchars(\$data['grn']->address)) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-card">
                    <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 6px;">Received At</div>
                    <div class="customer-name"><?= htmlspecialchars(\$data['company']->company_name) ?></div>
                    <div class="customer-details">
                        <?php if(!empty(\$data['company']->address)): ?>
                            <?= nl2br(htmlspecialchars(\$data['company']->address)) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive">
                <table class="table-items">
                    <thead>
                        <tr>
                            <th style="width: 40px;" class="center">#</th>
                            <th>Item Description</th>
                            <th class="num">Qty</th>
                            <th class="num">Unit Cost</th>
                            <th class="num" style="color: #1E40AF;">Retail</th>
                            <th class="num" style="color: #6B21A8;">Wholesale</th>
                            <th class="num">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php \$rowNum = 1; foreach(\$data['items'] as \$item): ?>
                        <?php \$lineTotal = floatval(\$item->quantity) * floatval(\$item->unit_cost); ?>
                        <tr>
                            <td class="center" style="color: #94A3B8;"><?= \$rowNum++ ?></td>
                            <td class="item-desc"><?= htmlspecialchars(\$item->description) ?></td>
                            <td class="num" style="font-weight: 700;"><?= number_format(\$item->quantity, 0) ?></td>
                            <td class="num"><?= number_format(\$item->unit_cost, 2) ?></td>
                            <td class="num" style="color: #1E40AF;"><?= number_format(\$item->retail_price, 2) ?></td>
                            <td class="num" style="color: #6B21A8; font-weight: 600;"><?= number_format(\$item->wholesale_price, 2) ?></td>
                            <td class="num" style="font-weight: 700; color: #0F172A;"><?= number_format(\$lineTotal, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Bottom Section -->
            <div class="bottom-section">
                <!-- Notes -->
                <div class="payment-info" style="background-color: #FFFFFF;">
                    <?php if(!empty(\$data['grn']->notes)): ?>
                    <div class="payment-title"><i class="ph-fill ph-info"></i> Inspection Notes</div>
                    <div class="terms-text" style="white-space: pre-wrap; font-size: 11.5px;"><?= htmlspecialchars(\$data['grn']->notes) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Totals -->
                <div class="summary-card">
                    <table class="table-totals">
                        <tr class="grand-total-row">
                            <th>Grand Total (Cost)</th>
                            <td><?= number_format(\$grandTotal, 2) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Signatures -->
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line" style="display: flex; align-items: flex-end; justify-content: center;">
                        <?php if(!empty(\$data['grn']->creator_signature)): ?>
                            <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars(\$data['grn']->creator_signature) ?>" style="max-height: 40px; max-width: 100%; object-fit: contain;">
                        <?php endif; ?>
                    </div>
                    <div class="signature-label">Received &amp; Verified By</div>
                    <div style="font-size: 10px; color: #64748B; margin-top: 2px;"><?= htmlspecialchars(\$data['grn']->creator_name) ?></div>
                </div>
                
                <div class="signature-box">
                    <div class="signature-line" style="display: flex; align-items: flex-end; justify-content: center;">
                        <?php if(\$data['grn']->is_approved): ?>
                            <span style="color: #15803D; border: 1.5px dashed #15803D; padding: 2px 6px; border-radius: 4px; font-weight: 800; font-size: 10px; text-transform: uppercase;">APPROVED</span>
                        <?php endif; ?>
                    </div>
                    <div class="signature-label">Approved By</div>
                    <?php if(\$data['grn']->is_approved): ?>
                    <div style="font-size: 10px; color: #64748B; margin-top: 2px;"><?= htmlspecialchars(\$data['grn']->approver_name) ?> (<?= date('M d, Y', strtotime(\$data['grn']->approved_at)) ?>)</div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <div class="document-footer">
            <div>Printed on <?= date('Y-m-d H:i') ?></div>
            <div>Powered by <?= APP_NAME ?> ERP</div>
        </div>
    </div>

</body>
</html>
PHP;

file_put_contents('app/Views/grns/view.php', $grnView);
echo "Updated GRN View!";
