<?php
$customer = $data['customer'];
$invoiceItems = $data['invoice_items'];
$delivery = $data['active_delivery'];
$payments = $data['payments'] ?? [];
$invoices = $data['invoices'] ?? [];
?>

<div style="margin-bottom: 15px;">
    <a href="<?= APP_URL ?>/driver" style="color: var(--primary); text-decoration: none; font-size: 14px; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;">
        ← Back to Hub
    </a>
</div>

<div class="card" style="padding: 20px; text-align: center; border-top: 5px solid var(--success); position: relative; overflow: hidden;">
    <!-- Decorative subtle background checkmark -->
    <div style="position: absolute; right: -20px; top: -20px; font-size: 120px; color: rgba(46,204,113,0.05); z-index: 1; pointer-events: none; transform: rotate(15deg);">✓</div>
    
    <div style="font-size: 48px; margin-bottom: 10px; position: relative; z-index: 2;">🏆</div>
    <span class="badge badge-success" style="margin-bottom: 15px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.8px; padding: 4px 10px; font-size: 11px;">
        Delivery Completed
    </span>
    
    <h2 style="margin: 0 0 8px; font-size: 20px; font-weight: 800; color: var(--text-dark); position: relative; z-index: 2;"><?= htmlspecialchars($customer->name) ?></h2>
    <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 4px; position: relative; z-index: 2;">📞 <?= htmlspecialchars($customer->phone) ?></div>
    <div style="font-size: 13px; color: var(--text-muted); position: relative; z-index: 2;">📍 <?= htmlspecialchars($customer->address ?: 'No Address listed') ?></div>
</div>

<!-- 1. DELIVERED PRODUCTS -->
<h3 style="font-size: 14px; font-weight: 800; text-transform: uppercase; margin: 25px 0 12px; color: var(--text-muted); letter-spacing: 0.5px;">📦 Delivered Products Summary</h3>
<div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 25px;">
    <?php if (empty($invoiceItems)): ?>
        <div class="card" style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 13px;">
            No items were delivered.
        </div>
    <?php else: ?>
        <?php foreach ($invoiceItems as $entry): 
            $item = $entry['item'];
            $invoiceNumber = $entry['invoice_number'];
        ?>
            <div class="card" style="margin: 0; padding: 15px; display: flex; flex-direction: column; gap: 8px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <strong style="font-size: 14px; color: var(--text-dark);"><?= htmlspecialchars($item->description) ?></strong>
                        <div style="margin-top: 4px;">
                            <span style="font-size: 10px; background: var(--primary-light); color: var(--primary); padding: 2px 6px; border-radius: 4px; display: inline-block;">
                                <?= htmlspecialchars($invoiceNumber) ?>
                            </span>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 12px; font-weight: 800; color: var(--success); background: rgba(46,204,113,0.1); padding: 2px 8px; border-radius: 20px;">
                            Qty: <?= floatval($item->quantity) ?>
                        </span>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 8px; border-top: 1px dashed var(--border); font-size: 12px;">
                    <span style="color: var(--text-muted);">Rs. <?= number_format($item->unit_price, 2) ?> each</span>
                    <strong style="color: var(--text-dark);">Rs. <?= number_format($item->total, 2) ?></strong>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- 2. PAYMENTS COLLECTED -->
<h3 style="font-size: 14px; font-weight: 800; text-transform: uppercase; margin: 25px 0 12px; color: var(--primary); letter-spacing: 0.5px;">💵 Collections Processed</h3>
<div class="card" style="padding: 15px; margin-bottom: 25px;">
    <?php if (empty($payments)): ?>
        <div style="text-align: center; color: var(--text-muted); font-size: 13px; padding: 10px 0;">
            No cash, bank, or cheque collections were recorded for this delivery checkout.
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php 
            $totalCollected = 0.0;
            foreach ($payments as $pmt): 
                $totalCollected += floatval($pmt->amount);
            ?>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; padding-bottom: 8px; border-bottom: 1px solid var(--border);">
                    <div>
                        <span class="badge" style="background: var(--primary-light); color: var(--primary); font-size: 10px; text-transform: uppercase; font-weight: bold;">
                            <?= htmlspecialchars($pmt->payment_method) ?>
                        </span>
                        <?php if (!empty($pmt->reference)): ?>
                            <span style="font-size: 11px; color: var(--text-muted); margin-left: 5px;">
                                (Ref: <?= htmlspecialchars($pmt->reference) ?>)
                            </span>
                        <?php endif; ?>
                    </div>
                    <strong style="font-family: monospace; color: var(--text-dark);">
                        Rs. <?= number_format($pmt->amount, 2) ?>
                    </strong>
                </div>
            <?php endforeach; ?>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px; padding-top: 5px; font-weight: bold;">
                <span style="color: var(--text-dark);">Total Amount Collected:</span>
                <span style="color: var(--success); font-size: 16px; font-family: monospace;">
                    Rs. <?= number_format($totalCollected, 2) ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- 3. INVOICES SETTLED -->
<h3 style="font-size: 14px; font-weight: 800; text-transform: uppercase; margin: 25px 0 12px; color: var(--text-muted); letter-spacing: 0.5px;">🧾 Invoices & Bills Delivered</h3>
<div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 30px;">
    <?php foreach ($invoices as $inv): ?>
        <div class="card" style="margin: 0; padding: 12px 15px; display: flex; justify-content: space-between; align-items: center; border-left: 4px solid var(--success);">
            <div>
                <strong style="font-size: 13px; color: var(--text-dark);"><?= htmlspecialchars($inv->invoice_number) ?></strong>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                    Date: <?= htmlspecialchars($inv->invoice_date) ?> | Status: <span style="color: var(--success); font-weight: bold;">Delivered</span>
                </div>
            </div>
            <strong style="font-size: 14px; color: var(--text-dark);">
                Rs. <?= number_format($inv->true_grand_total, 2) ?>
            </strong>
        </div>
    <?php endforeach; ?>
</div>

<div style="margin-bottom: 40px;">
    <a href="<?= APP_URL ?>/driver" class="btn-primary" style="background: var(--primary); text-align: center; text-decoration: none; display: block;">
        Done & Return to Hub
    </a>
</div>
