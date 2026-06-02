<?php
$customer = $data['customer'];
$invoiceItems = array_filter($data['invoice_items'] ?? [], function($entry) {
    return floatval($entry['item']->quantity ?? 0) > 0;
});
$delivery = $data['active_delivery'];
?>

<div style="margin-bottom: 15px;">
    <a href="<?= APP_URL ?>/driver" style="color: var(--primary); text-decoration: none; font-size: 14px; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;">
        ← Back to Hub
    </a>
</div>

<div class="card" style="padding: 15px; margin-bottom: 20px; border-top: 4px solid var(--primary);">
    <h2 style="margin: 0 0 8px; font-size: 18px; font-weight: 800;"><?= htmlspecialchars($customer->name) ?></h2>
    <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">📞 <?= htmlspecialchars($customer->phone) ?></div>
    <div style="font-size: 13px; color: var(--text-muted);">📍 <?= htmlspecialchars($customer->address ?: 'No Address listed') ?></div>
</div>

<?php if (!empty($data['credit_bills'])): ?>
    <h3 style="font-size: 14px; font-weight: 800; text-transform: uppercase; margin: 25px 0 10px; color: var(--danger); letter-spacing: 0.5px;">💳 Outstanding Credit Bills</h3>
    <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 25px;">
        <?php foreach ($data['credit_bills'] as $bill): ?>
            <a href="<?= APP_URL ?>/driver/billing/checkout/<?= $bill->customer_id ?>" style="text-decoration: none; display: block; color: inherit;">
                <div class="card card-interactive" style="margin: 0; padding: 12px 15px; border-left: 4px solid var(--danger); background: var(--surface); display: flex; justify-content: space-between; align-items: center; transition: all 0.2s ease;">
                    <div style="flex: 1; padding-right: 10px;">
                        <strong style="font-size: 14px; color: var(--text-dark);"><?= htmlspecialchars($bill->invoice_number) ?></strong>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Date: <?= htmlspecialchars($bill->invoice_date) ?></div>
                    </div>
                    <div style="text-align: right; display: flex; align-items: center; gap: 10px;">
                        <div>
                            <span class="badge badge-danger" style="font-size: 9px; padding: 3px 6px;">Unpaid</span>
                            <strong style="font-size: 14px; display: block; margin-top: 4px; color: var(--danger);">Rs. <?= number_format($bill->true_grand_total, 2) ?></strong>
                        </div>
                        <span style="font-size: 16px; color: var(--text-muted);">❯</span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h3 style="font-size: 14px; font-weight: 800; text-transform: uppercase; margin: 20px 0 10px; color: var(--text-muted); letter-spacing: 0.5px;">Product Checklist</h3>

<?php if (empty($invoiceItems)): ?>
    <div class="card" style="text-align: center; padding: 30px;">
        <span style="font-size: 40px;">🛒</span>
        <h4 style="margin: 15px 0 5px;">All Items Delivered</h4>
        <p style="font-size: 13px; color: var(--text-muted);">There are no pending products to deliver to this shop today.</p>
        <a href="<?= APP_URL ?>/driver" class="btn-primary">Return to Dashboard</a>
    </div>
<?php else: ?>

    <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px;">
        <?php foreach ($invoiceItems as $entry): 
            $item = $entry['item'];
            $invoiceNumber = $entry['invoice_number'];
        ?>
            <div class="card" id="item-card-<?= $item->id ?>" style="margin: 0; padding: 15px; display: flex; flex-direction: column; gap: 12px; position: relative;">
                
                <!-- TOP CHECKBOX AND NAME -->
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <input type="checkbox" class="confirm-chk" id="chk-<?= $item->id ?>" onchange="checkAllTicked()" style="width: 22px; height: 22px; cursor: pointer; accent-color: var(--success); margin-top: 2px;">
                    <div style="flex: 1;">
                        <label for="chk-<?= $item->id ?>" style="font-size: 15px; font-weight: 700; cursor: pointer; display: block; line-height: 1.3;">
                            <?= htmlspecialchars($item->description) ?>
                        </label>
                        <span style="font-size: 11px; color: var(--text-muted); background: var(--primary-light); color: var(--primary); padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 4px;">
                            <?= htmlspecialchars($invoiceNumber) ?>
                        </span>
                    </div>
                    
                    <!-- DELETE/REMOVE PRODUCT -->
                    <button type="button" onclick="deleteProduct(<?= $item->id ?>)" style="background: none; border: none; font-size: 18px; color: var(--danger); cursor: pointer; padding: 4px;">
                        🗑
                    </button>
                </div>

                <!-- BOTTOM SPINNER AND PRICES -->
                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 8px; border-top: 1px dashed var(--border);">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button type="button" class="btn-spinner" onclick="adjustQty(<?= $item->id ?>, <?= floatval($item->quantity) - 1 ?>)" style="width: 32px; height: 32px; border-radius: 50%; border: 1px solid var(--border); background: var(--surface); color: var(--text-dark); font-weight: bold; font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center;">-</button>
                        <strong style="font-size: 15px; min-width: 30px; text-align: center;"><?= floatval($item->quantity) ?></strong>
                        <button type="button" class="btn-spinner" onclick="adjustQty(<?= $item->id ?>, <?= floatval($item->quantity) + 1 ?>)" style="width: 32px; height: 32px; border-radius: 50%; border: 1px solid var(--border); background: var(--surface); color: var(--text-dark); font-weight: bold; font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center;">+</button>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 11px; color: var(--text-muted);">Rs. <?= number_format($item->unit_price, 2) ?> each</div>
                        <strong style="font-size: 15px; color: var(--text-dark);">Rs. <?= number_format($item->total, 2) ?></strong>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

    <!-- BOTTOM SUMMARY & CHECKOUT BLOCKER -->
    <div class="card" style="position: sticky; bottom: 10px; margin-bottom: 0; box-shadow: 0 -4px 15px rgba(0,0,0,0.08); border-top: 3px solid var(--primary); z-index: 100;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <span style="font-size: 14px; font-weight: 700; color: var(--text-muted);">Checklist Progress:</span>
            <strong id="progress-text" style="font-size: 14px; color: var(--text-dark);">0 / <?= count($invoiceItems) ?> Ticked</strong>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <span style="font-size: 14px; font-weight: 700; color: var(--text-muted);">Today's Revised Due:</span>
            <strong style="font-size: 18px; color: var(--primary);">
                Rs. <?php 
                    $todaySum = 0;
                    foreach($data['invoices'] as $inv) { $todaySum += floatval($inv->true_grand_total); }
                    echo number_format($todaySum, 2);
                ?>
            </strong>
        </div>
        
        <a id="btn-checkout" href="<?= APP_URL ?>/driver/billing/checkout/<?= $customer->id ?>" class="btn-primary btn-disabled" onclick="return handleCheckoutClick(event)">
            Proceed to POS Checkout
        </a>
    </div>

<?php endif; ?>

<!-- LOADING OVERLAY -->
<div id="loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: var(--surface); padding: 25px; border-radius: 12px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
        <div style="border: 4px solid var(--border); border-top: 4px solid var(--primary); border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 15px;"></div>
        <strong style="font-size: 14px;">Updating Delivery...</strong>
    </div>
</div>

<style>
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<script>
    function checkAllTicked() {
        var checkboxes = document.querySelectorAll('.confirm-chk');
        var checkedCount = document.querySelectorAll('.confirm-chk:checked').length;
        var totalCount = checkboxes.length;

        document.getElementById('progress-text').innerText = checkedCount + ' / ' + totalCount + ' Ticked';

        var checkoutBtn = document.getElementById('btn-checkout');
        if (checkedCount === totalCount && totalCount > 0) {
            checkoutBtn.classList.remove('btn-disabled');
            checkoutBtn.style.pointerEvents = 'auto';
        } else {
            checkoutBtn.classList.add('btn-disabled');
            checkoutBtn.style.pointerEvents = 'none';
        }
    }

    function handleCheckoutClick(e) {
        var checkedCount = document.querySelectorAll('.confirm-chk:checked').length;
        var totalCount = document.querySelectorAll('.confirm-chk').length;
        if (checkedCount !== totalCount || totalCount === 0) {
            e.preventDefault();
            alert("All items in the checklist must be ticked off as 'Given' before you can proceed to the checkout terminal.");
            return false;
        }
        return true;
    }

    function showLoading() {
        document.getElementById('loading-overlay').style.display = 'flex';
    }

    function adjustQty(itemId, newQty) {
        if (newQty <= 0) {
            if (confirm("Are you sure you want to remove this product from the invoice checklist?")) {
                deleteProduct(itemId);
            }
            return;
        }

        showLoading();
        fetch('<?= APP_URL ?>/driver/billing/api_update_item', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update',
                item_id: itemId,
                quantity: newQty
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.status === 'success') {
                window.location.reload();
            } else {
                alert(data.message || "Failed to adjust product quantity.");
                document.getElementById('loading-overlay').style.display = 'none';
            }
        })
        .catch(function(err) {
            console.error(err);
            alert("An error occurred during quantity update.");
            document.getElementById('loading-overlay').style.display = 'none';
        });
    }

    function deleteProduct(itemId) {
        if (!confirm("Are you sure you want to completely delete this item from the invoice? Reserved stock will be automatically freed.")) {
            return;
        }

        showLoading();
        fetch('<?= APP_URL ?>/driver/billing/api_update_item', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'delete',
                item_id: itemId
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.status === 'success') {
                window.location.reload();
            } else {
                alert(data.message || "Failed to delete product.");
                document.getElementById('loading-overlay').style.display = 'none';
            }
        })
        .catch(function(err) {
            console.error(err);
            alert("An error occurred while deleting the product.");
            document.getElementById('loading-overlay').style.display = 'none';
        });
    }

    // Run on view load to set initial state
    document.addEventListener("DOMContentLoaded", function() {
        checkAllTicked();
    });
</script>
