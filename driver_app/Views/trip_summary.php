<?php
$delivery = $data['delivery'];
$collections = $data['collections'];

$start = floatval($delivery->start_meter);
$end = floatval($delivery->end_meter);
$distance = max(0, $end - $start);
?>

<div class="card" style="text-align: center; border-top: 4px solid var(--primary); padding: 30px 20px;">
    <div style="font-size: 55px; margin-bottom: 15px;">🏁</div>
    <span class="badge badge-info" style="margin-bottom: 12px;">Route Completed</span>
    <h2 style="margin: 0 0 10px; font-size: 22px; font-weight: 800;"><?= htmlspecialchars($delivery->route_name) ?></h2>
    <p style="margin: 0 0 25px; font-size: 14px; color: var(--text-muted);">Congratulations! The daily delivery route trip has been concluded and odometer statements have been registered.</p>

    <!-- ODOMETER DETAILS -->
    <div style="background: var(--app-bg); border-radius: 14px; padding: 20px; text-align: left; border: 1px solid var(--border); margin-bottom: 25px; display: flex; flex-direction: column; gap: 12px;">
        <h4 style="margin: 0 0 5px; font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px;">Trip Mileage Stats</h4>
        
        <div style="display: flex; justify-content: space-between; font-size: 14px;">
            <span style="color: var(--text-muted);">Start Odometer:</span>
            <strong><?= number_format($start, 2) ?> KM</strong>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 14px;">
            <span style="color: var(--text-muted);">End Odometer:</span>
            <strong><?= number_format($end, 2) ?> KM</strong>
        </div>
        <div style="display: flex; justify-content: space-between; padding-top: 10px; border-top: 1px dashed var(--border); font-size: 15px;">
            <strong style="color: var(--text-dark);">Distance Traveled:</strong>
            <strong style="color: var(--primary);"><?= number_format($distance, 2) ?> KM</strong>
        </div>
    </div>

    <!-- COLLECTIONS GROUPING -->
    <div style="background: var(--app-bg); border-radius: 14px; padding: 20px; text-align: left; border: 1px solid var(--border); margin-bottom: 10px; display: flex; flex-direction: column; gap: 12px;">
        <h4 style="margin: 0 0 5px; font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px;">Trip Collection Reports</h4>
        
        <?php 
        $grandTotalCollections = 0.0;
        if (empty($collections)): 
        ?>
            <div style="font-size: 13px; color: var(--text-muted); text-align: center; padding: 10px 0;">No payment collections logged today.</div>
        <?php else: ?>
            <?php foreach ($collections as $c): 
                $grandTotalCollections += floatval($c->total_collected);
            ?>
                <div style="display: flex; justify-content: space-between; font-size: 14px;">
                    <span style="color: var(--text-muted);"><?= htmlspecialchars($c->payment_method) ?> (<?= $c->tx_count ?>):</span>
                    <strong>Rs. <?= number_format($c->total_collected, 2) ?></strong>
                </div>
            <?php endforeach; ?>
            
            <div style="display: flex; justify-content: space-between; padding-top: 10px; border-top: 1px dashed var(--border); font-size: 15px;">
                <strong style="color: var(--text-dark);">Total Collected:</strong>
                <strong style="color: var(--success);">Rs. <?= number_format($grandTotalCollections, 2) ?></strong>
            </div>
        <?php endif; ?>
    </div>
</div>

<div style="margin-top: 20px; margin-bottom: 40px; display: flex; flex-direction: column; gap: 12px;">
    <a href="<?= APP_URL ?>/driver" class="btn-primary" style="background: var(--primary);">
        🏠 Go to Driver Dashboard
    </a>
    <a href="<?= APP_URL ?>/dashboard" class="btn-secondary" style="background: var(--border); color: var(--text-dark);">
        Exit to ERP Admin
    </a>
</div>
