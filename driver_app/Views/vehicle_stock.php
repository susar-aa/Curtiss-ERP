<?php
$delivery = $data['active_delivery'];
$stockItems = $data['stock_items'];
?>

<div style="padding: 10px 0;">
    <h2 style="font-size: 20px; font-weight: 800; margin: 0 0 5px; color: var(--text-dark);">🚚 Vehicle Stock Balance</h2>
    <p style="color: var(--text-muted); font-size: 13px; margin: 0 0 20px;">Live inventory load and remaining balances inside your vehicle.</p>

    <?php if (!$delivery): ?>
        <div class="card" style="text-align: center; padding: 40px 20px;">
            <span style="font-size: 48px;">😴</span>
            <h3 style="margin-top: 15px; font-size: 16px;">No Active Route Session</h3>
            <p style="color: var(--text-muted); font-size: 13px;">Please start a route delivery to view your vehicle stock.</p>
            <a href="<?= APP_URL ?>/driver" class="btn-primary" style="margin-top: 15px; display: inline-block; width: auto; padding: 10px 20px;">Go to Dashboard</a>
        </div>
    <?php else: ?>
        <!-- ROUTE HEADER METADATA -->
        <div class="card" style="background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%); color: white; border: none; margin-bottom: 20px;">
            <span class="badge" style="background: rgba(255,255,255,0.2); color: white; margin-bottom: 10px; font-size: 10px; width: fit-content;">Route: <?= htmlspecialchars($delivery->route_name) ?></span>
            <h3 style="margin: 0 0 10px; font-size: 18px; font-weight: 800;"><?= htmlspecialchars($delivery->vehicle_number) ?></h3>
            <div style="display: flex; gap: 15px; font-size: 12px; opacity: 0.9;">
                <span>👤 Driver: <?= htmlspecialchars($delivery->driver_name ?: 'Pending') ?></span>
                <span>📅 Date: <?= htmlspecialchars($delivery->delivery_date) ?></span>
            </div>
        </div>

        <!-- SEARCH / FILTER -->
        <div style="margin-bottom: 15px;">
            <input type="text" id="stock-search" oninput="filterStock()" placeholder="🔍 Search vehicle items..." style="width: 100%; padding: 12px 15px; border-radius: 8px; border: 1px solid var(--border); background: var(--surface); color: var(--text-dark); font-size: 14px; box-sizing: border-box; outline: none; transition: border-color 0.2s;">
        </div>

        <?php if (empty($stockItems)): ?>
            <div class="card" style="text-align: center; padding: 30px 15px;">
                <p style="color: var(--text-muted); font-size: 14px; margin: 0;">No items have been loaded/reserved for this route.</p>
            </div>
        <?php else: ?>
            <div id="stock-list" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 30px;">
                <?php foreach ($stockItems as $item): ?>
                    <div class="card stock-item-card" data-name="<?= htmlspecialchars(strtolower($item->item_name)) ?>" style="margin: 0; padding: 15px; transition: transform 0.2s ease;">
                        <div style="font-weight: 700; font-size: 14px; color: var(--text-dark); margin-bottom: 12px; line-height: 1.4;">
                            <?= htmlspecialchars($item->item_name) ?>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; background: var(--app-bg); padding: 10px; border-radius: 8px; border: 1px solid var(--border); text-align: center;">
                            <div>
                                <span style="font-size: 10px; color: var(--text-muted); display: block; text-transform: uppercase; margin-bottom: 4px;">Loaded</span>
                                <strong style="font-size: 14px; color: var(--text-dark);"><?= number_format($item->loaded_qty, 0) ?></strong>
                            </div>
                            <div>
                                <span style="font-size: 10px; color: var(--text-muted); display: block; text-transform: uppercase; margin-bottom: 4px;">Delivered</span>
                                <strong style="font-size: 14px; color: var(--success);"><?= number_format($item->delivered_qty, 0) ?></strong>
                            </div>
                            <div>
                                <span style="font-size: 10px; color: var(--text-muted); display: block; text-transform: uppercase; margin-bottom: 4px;">Remaining</span>
                                <?php if ($item->remaining_qty <= 0): ?>
                                    <strong style="font-size: 14px; color: var(--text-muted); text-decoration: line-through;">0</strong>
                                <?php else: ?>
                                    <strong style="font-size: 15px; color: var(--primary);"><?= number_format($item->remaining_qty, 0) ?></strong>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    function filterStock() {
        var query = document.getElementById('stock-search').value.toLowerCase().trim();
        var cards = document.querySelectorAll('.stock-item-card');
        
        for (var i = 0; i < cards.length; i++) {
            var name = cards[i].getAttribute('data-name');
            if (name.indexOf(query) !== -1) {
                cards[i].style.display = 'block';
            } else {
                cards[i].style.display = 'none';
            }
        }
    }
</script>
