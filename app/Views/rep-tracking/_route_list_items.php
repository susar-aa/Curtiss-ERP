<?php foreach($routes as $route): ?>
    <?php 
        $status = $route->status;
        if ($status === 'Active') {
            $dataType = 'active';
        } elseif ($status === 'Pending GL') {
            $dataType = 'pending_gl';
        } elseif ($status === 'Adjustments') {
            $dataType = 'adjustments';
        } elseif ($status === 'Loading') {
            $dataType = 'loading';
        } elseif ($status === 'Variance Adjustment') {
            $dataType = 'variance';
        } elseif ($status === 'Finalizing' || $status === 'Delivery Arranged') {
            $dataType = 'finalizing';
        } else {
            $dataType = 'completed';
        }
    ?>
    <div class="route-item" id="route_<?= $route->id ?>" data-route-type="<?= $dataType ?>" onclick="loadRouteDetails(<?= $route->id ?>, this)" style="cursor: pointer; border: 1px solid var(--mac-border); border-radius: 8px; padding: 15px; margin-bottom: 12px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between;">
        
        <!-- Top row: Route Number and status badge -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span style="font-family: monospace; font-weight: bold; background: rgba(0, 102, 204, 0.1); color: #0066cc; padding: 2px 6px; border-radius: 4px; font-size: 11px;">
                #RT-<?= str_pad($route->id, 5, '0', STR_PAD_LEFT) ?>
            </span>
            <span style="font-size: 10px; font-weight: bold; padding: 2px 8px; border-radius: 12px; background: <?= ($route->status === 'Completed' || $route->status === 'Finalized') ? '#e2f0d9' : '#fff3cd' ?>; color: <?= ($route->status === 'Completed' || $route->status === 'Finalized') ? '#2e7d32' : '#d97706' ?>; border: 1px solid <?= ($route->status === 'Completed' || $route->status === 'Finalized') ? '#2e7d32' : '#d97706' ?>;">
                <?= htmlspecialchars($route->status) ?>
            </span>
        </div>

        <!-- Route Name -->
        <div class="r-title" style="font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 6px; line-height: 1.2;">
            <?= htmlspecialchars($route->route_name) ?>
        </div>

        <!-- Rep Name -->
        <div class="r-sub" style="font-size: 11px; color: #64748b; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;">
            Rep: <?= htmlspecialchars($route->first_name . ' ' . $route->last_name) ?>
        </div>

        <!-- Meta stats -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; padding: 8px 0; margin-bottom: 8px;">
            <div>
                <div style="font-size: 9px; color: #94a3b8; text-transform: uppercase; font-weight: bold;">Route Total</div>
                <div style="font-size: 12px; font-weight: bold; color: #2e7d32; font-family: monospace;">Rs <?= number_format($route->total_sales, 2) ?></div>
            </div>
            <div>
                <div style="font-size: 9px; color: #94a3b8; text-transform: uppercase; font-weight: bold;">Customers</div>
                <div style="font-size: 12px; font-weight: bold; color: #1e293b; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="ph ph-users" style="color: #64748b; font-size: 14px;"></i> <?= intval($route->customer_count ?? 0) ?>
                </div>
            </div>
        </div>

        <!-- Footer line: Last Updated -->
        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 10px; color: #94a3b8;">
            <span>Updated: <strong><?= date('M d, Y H:i', strtotime($route->created_at ?? $route->start_time)) ?></strong></span>
        </div>

        <?php if (!empty($route->is_bound_group)): ?>
            <div class="rb-bound-tag" style="background: #e0f2fe; color: #0369a1; display: inline-flex; align-items: center; gap: 4px; margin-top: 8px; font-size: 10px; border-radius: 4px; padding: 4px 8px;">
                <i class="ph ph-link"></i> Group: <?= htmlspecialchars($route->constituent_routes_info) ?>
            </div>
        <?php elseif (!empty($route->binding_name)): ?>
            <div class="rb-bound-tag" style="display: inline-flex; align-items: center; gap: 4px; margin-top: 8px; font-size: 10px; border-radius: 4px; padding: 4px 8px;">
                <i class="ph ph-link"></i> Bound: <?= htmlspecialchars($route->binding_name) ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Hidden data payload -->
    <div id="route_data_<?= $route->id ?>" style="display:none;" 
         data-rep="<?= htmlspecialchars($route->first_name . ' ' . $route->last_name) ?>"
         data-rname="<?= htmlspecialchars($route->route_name) ?>"
         data-date="<?= date('Y-m-d', strtotime($route->start_time)) ?>"
         data-territory="<?= htmlspecialchars($route->route_name) ?>"
         data-constituent="<?= htmlspecialchars($route->constituent_routes_info ?? '') ?>"
         data-start="<?= $route->start_meter ?>"
         data-end="<?= $route->end_meter ?: 'Active' ?>"
          data-start-time="<?= date('Y-m-d H:i:s', strtotime($route->start_time)) ?>"
          data-end-time="<?= $route->end_time ? date('Y-m-d H:i:s', strtotime($route->end_time)) : 'Active' ?>"
         data-sales="<?= number_format($route->total_sales, 2) ?>"
         data-bills="<?= $route->bill_count ?>"
         data-status="<?= $route->status ?>"
         data-unfinalized="<?= $route->unfinalized_count ?>"
         data-bound="<?= !empty($route->is_bound_group) ? '1' : '0' ?>"
         data-binding-id="<?= $route->route_binding_id ?: '' ?>"
         data-delivery-id="<?= $route->delivery_id ?: '' ?>"
         data-delivery-status="<?= $route->delivery_status ?: '' ?>"
         data-merged="<?= $route->is_merged_route ? '1' : '0' ?>">
    </div>
<?php endforeach; ?>
