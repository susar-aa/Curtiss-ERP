<?php
$invoicePath = 'app/Views/sales/invoice_view.php';
$c = file_get_contents($invoicePath);

// Replace invoice specific variables with estimate variables
// Invoice uses $invoice, $items, $company

// In invoice_view.php, the variables are likely $data['invoice'], $data['items'], $data['company']
$c = str_replace('$data[\'invoice\']', '$data[\'estimate\']', $c);
$c = str_replace('$invoice', '$data[\'estimate\']', $c);

// Titles and headers
$c = str_replace('Invoice <?= htmlspecialchars($data[\'estimate\']->invoice_number) ?>', 'Estimate <?= htmlspecialchars($data[\'estimate\']->estimate_number) ?>', $c);
$c = str_replace('<title>Invoice', '<title>Estimate', $c);
$c = str_replace('<div class="doc-type">INVOICE</div>', '<div class="doc-type">ESTIMATE</div>', $c);
$c = str_replace('<div class="doc-type-bg">INVOICE</div>', '<div class="doc-type-bg">ESTIMATE</div>', $c);

// For meta labels
$c = str_replace('<div class="meta-label">Invoice No:</div>', '<div class="meta-label">Estimate No:</div>', $c);
$c = str_replace('<div class="meta-value" style="font-size: 15px; font-weight: 800; color: var(--color-primary);">
                                <?= htmlspecialchars($data[\'estimate\']->invoice_number) ?>
                            </div>', '<div class="meta-value" style="font-size: 15px; font-weight: 800; color: var(--color-primary);">
                                <?= htmlspecialchars($data[\'estimate\']->estimate_number) ?>
                            </div>', $c);
$c = str_replace('<?= htmlspecialchars($data[\'estimate\']->invoice_number) ?>', '<?= htmlspecialchars($data[\'estimate\']->estimate_number) ?>', $c);
$c = str_replace('<div class="meta-label">Invoice Date:</div>', '<div class="meta-label">Estimate Date:</div>', $c);
$c = str_replace('<?= date(\'M d, Y\', strtotime($data[\'estimate\']->invoice_date)) ?>', '<?= date(\'M d, Y\', strtotime($data[\'estimate\']->estimate_date)) ?>', $c);
$c = str_replace('<div class="meta-label">Due Date:</div>', '<div class="meta-label">Valid Until:</div>', $c);
$c = str_replace('<?= date(\'M d, Y\', strtotime($data[\'estimate\']->due_date)) ?>', '<?= date(\'M d, Y\', strtotime($data[\'estimate\']->expiry_date)) ?>', $c);
$c = str_replace('<div class="meta-label">PO Ref:</div>', '<div class="meta-label">Status:</div>', $c);
$c = str_replace('<?= htmlspecialchars($data[\'estimate\']->po_reference ?? \'N/A\') ?>', '<?= htmlspecialchars($data[\'estimate\']->status ?? \'Draft\') ?>', $c);

// Bill To / Customer
$c = str_replace('Billed To', 'Estimate For', $c);

// Back button URL
$c = str_replace('href="<?= APP_URL ?>/sales"', 'href="<?= APP_URL ?>/estimate"', $c);
$c = str_replace('Back to Sales', 'Back to Estimates', $c);

// Payment link removal
// Invoices usually have payment links, let's just let it be or hide them if present.
// We can use regex to remove payment button if it exists
$c = preg_replace('/<a href="<\?= APP_URL \?>\/sales\/payment_link.*?<\/a>/s', '', $c);

// Edit button injection
$editBtn = <<<EOD
                <?php if(\$data['estimate']->status !== 'Invoiced'): ?>
                    <a href="<?= APP_URL ?>/estimate/edit/<?= \$data['estimate']->id ?>" class="action-btn" style="background:#f1f5f9; color:#0f172a; border: 1px solid #cbd5e1; text-decoration: none;">
                        <i class="fa-solid fa-pencil"></i> Edit Quotation
                    </a>
                <?php endif; ?>
EOD;

$c = str_replace(
    '<button onclick="window.print()" class="action-btn action-print">',
    $editBtn . "\n                <button onclick=\"window.print()\" class=\"action-btn action-print\">",
    $c
);

// We should also replace the variables that are not present in estimate
$c = str_replace('<?= number_format($data[\'estimate\']->subtotal, 2) ?>', '<?= number_format($data[\'estimate\']->total_amount, 2) ?>', $c);
$c = str_replace('<?= number_format($data[\'estimate\']->grand_total, 2) ?>', '<?= number_format($data[\'estimate\']->total_amount, 2) ?>', $c);

file_put_contents('app/Views/estimates/show.php', $c);
echo "Updated Estimate View!";
