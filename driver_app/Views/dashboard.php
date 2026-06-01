<?php
$delivery = $data['active_delivery'];
$employees = $data['employees'];
?>

<?php if (!$delivery): ?>
    <div style="text-align: center; padding: 40px 20px;">
        <span style="font-size: 64px;">😴</span>
        <h3 style="margin-top: 20px; font-size: 20px;">No Active Assignments</h3>
        <p style="color: var(--text-muted); font-size: 14px; line-height: 1.5;">You do not have any arranged or active delivery routes assigned to you at the moment.</p>
        <a href="<?= APP_URL ?>/driver" class="btn-primary" style="margin-top: 15px;">Refresh Dashboard</a>
    </div>
<?php else: ?>

    <?php if ($delivery->status === 'Arranged'): ?>
        <!-- 1. ARRANGED STATE -->
        <div class="card" style="text-align: center;">
            <div style="font-size: 40px; margin-bottom: 10px;">📋</div>
            <span class="badge badge-warning" style="margin-bottom: 15px;">Arranged Route</span>
            <h2 style="margin: 0 0 20px; font-size: 22px; font-weight: 800;"><?= htmlspecialchars($delivery->route_name) ?></h2>
            
            <div style="text-align: left; background: var(--app-bg); border-radius: 12px; padding: 15px; margin-bottom: 25px; border: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px;">
                    <span style="color: var(--text-muted);">Delivery Date:</span>
                    <strong><?= htmlspecialchars($delivery->delivery_date) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px;">
                    <span style="color: var(--text-muted);">Vehicle Number:</span>
                    <strong><?= htmlspecialchars($delivery->vehicle_number) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px;">
                    <span style="color: var(--text-muted);">Assigned Driver:</span>
                    <strong><?= htmlspecialchars($delivery->driver_name) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 14px;">
                    <span style="color: var(--text-muted);">Helper/Partner:</span>
                    <strong><?= htmlspecialchars($delivery->partner_name ?: 'None') ?></strong>
                </div>
            </div>

            <form action="<?= APP_URL ?>/driver/accept" method="POST">
                <input type="hidden" name="delivery_id" value="<?= $delivery->id ?>">
                <button type="submit" class="btn-primary" style="background: var(--primary);">Accept Route Plan</button>
            </form>
        </div>

    <?php elseif ($delivery->status === 'Accepted'): ?>
        <!-- 2. ACCEPTED STATE -->
        <form action="<?= APP_URL ?>/driver/start_trip" method="POST" class="card">
            <input type="hidden" name="delivery_id" value="<?= $delivery->id ?>">
            
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="font-size: 40px;">🔑</div>
                <h2 style="margin: 8px 0; font-size: 20px; font-weight: 800;">Start Trip Setup</h2>
                <span class="badge badge-info"><?= htmlspecialchars($delivery->route_name) ?></span>
            </div>

            <label class="form-label">Starting Odometer Reading (KM)</label>
            <input type="number" step="0.01" name="start_meter" required class="form-input" placeholder="e.g. 12040.50">

            <!-- SWAP DRIVERS / HELPERS SELECTS -->
            <label class="form-label">Driver Profile (Verify/Swap)</label>
            <select name="driver_name" required>
                <?php foreach ($employees as $e): ?>
                    <option value="<?= htmlspecialchars($e->full_name) ?>" <?= strcasecmp($e->full_name, $delivery->driver_name) === 0 ? 'selected' : '' ?>>
                        👤 <?= htmlspecialchars($e->full_name) ?> (<?= htmlspecialchars($e->job_title) ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <label class="form-label">Partner/Helper (Verify/Swap)</label>
            <select name="partner_name">
                <option value="">None</option>
                <?php foreach ($employees as $e): ?>
                    <option value="<?= htmlspecialchars($e->full_name) ?>" <?= strcasecmp($e->full_name, $delivery->partner_name ?? '') === 0 ? 'selected' : '' ?>>
                        🤝 <?= htmlspecialchars($e->full_name) ?> (<?= htmlspecialchars($e->job_title) ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-primary" style="background: var(--success); box-shadow: 0 4px 12px rgba(46, 204, 113, 0.2); margin-top: 10px;">Start Route Trip</button>
        </form>

    <?php elseif ($delivery->status === 'In Transit'): ?>
        <!-- 3. IN TRANSIT STATE -->
        <div class="card" style="padding: 15px; margin-bottom: 15px; border-left: 5px solid var(--primary);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700;"><?= htmlspecialchars($delivery->route_name) ?></h3>
                    <span style="font-size: 12px; color: var(--text-muted);">Vehicle: <?= htmlspecialchars($delivery->vehicle_number) ?></span>
                </div>
                <div style="text-align: right;">
                    <span class="badge badge-success">In Transit</span>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Start: <?= htmlspecialchars($delivery->start_meter) ?> KM</div>
                </div>
            </div>
        </div>

        <h3 style="font-size: 15px; font-weight: 800; text-transform: uppercase; margin: 25px 0 12px; color: var(--text-muted); letter-spacing: 0.5px;">Shops on Route</h3>
        
        <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 30px;">
            <?php foreach ($data['shops'] as $shop): ?>
                <?php 
                $isDelivered = intval($shop->pending_count) === 0;
                ?>
                <a href="<?= APP_URL ?>/driver/billing/shop/<?= $shop->id ?>" style="text-decoration: none; display: block; color: inherit;">
                    <div class="card" style="margin: 0; padding: 15px; transition: all 0.2s; border: 1px solid <?= $isDelivered ? 'rgba(46,204,113,0.3)' : 'var(--border)' ?>; background: <?= $isDelivered ? 'rgba(46,204,113,0.03)' : 'var(--surface)' ?>;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                            <strong style="font-size: 15px; font-weight: 700;"><?= htmlspecialchars($shop->name) ?></strong>
                            <span class="badge <?= $isDelivered ? 'badge-success' : 'badge-warning' ?>" style="font-size: 10px;">
                                <?= $isDelivered ? 'Delivered' : 'Pending' ?>
                            </span>
                        </div>
                        <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">
                            📍 <?= htmlspecialchars($shop->address ?: 'No Address listed') ?>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 12px; padding-top: 8px; border-top: 1px solid var(--border);">
                            <span style="color: var(--text-muted);">Invoices: <strong><?= $shop->invoice_count ?></strong></span>
                            <strong>Rs. <?= number_format($shop->total_amount, 2) ?></strong>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($data['route_credit_bills'])): ?>
            <h3 style="font-size: 15px; font-weight: 800; text-transform: uppercase; margin: 25px 0 12px; color: var(--danger); letter-spacing: 0.5px;">💳 Outstanding Credit Collections on Route</h3>
            <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 30px;">
                <?php foreach ($data['route_credit_bills'] as $bill): 
                    $isCompleted = !empty($bill->is_completed);
                ?>
                    <a href="<?= APP_URL ?>/driver/billing/shop/<?= $bill->customer_id ?>" style="text-decoration: none; display: block; color: inherit;">
                        <div class="card card-interactive" style="margin: 0; padding: 12px 15px; border-left: 4px solid <?= $isCompleted ? 'var(--success)' : 'var(--danger)' ?>; background: <?= $isCompleted ? 'rgba(46,204,113,0.03)' : 'var(--surface)' ?>; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s ease;">
                            <div style="flex: 1; padding-right: 10px;">
                                <strong style="font-size: 14px; color: var(--text-dark);"><?= htmlspecialchars($bill->customer_name) ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                    Outstanding Customer Credit Collection
                                </div>
                            </div>
                            <div style="text-align: right; display: flex; align-items: center; gap: 10px;">
                                <div>
                                    <span class="badge <?= $isCompleted ? 'badge-success' : 'badge-danger' ?>" style="font-size: 9px; padding: 2px 6px;">
                                        <?= $isCompleted ? 'Delivered' : 'Pending' ?>
                                    </span>
                                    <strong style="font-size: 14px; display: block; margin-top: 4px; color: <?= $isCompleted ? 'var(--success)' : 'var(--danger)' ?>;">
                                        Rs. <?= number_format($bill->true_grand_total, 2) ?>
                                    </strong>
                                </div>
                                <span style="font-size: 16px; color: var(--text-muted);">❯</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- END TRIP SECTION -->
        <div class="card" style="border-top: 5px solid var(--danger);">
            <h3 style="margin: 0 0 15px; font-size: 16px; font-weight: 800;">Conclude Daily Delivery Route</h3>
            
            <button type="button" id="btn-show-end-day" class="btn-primary" style="background: var(--danger); box-shadow: 0 4px 12px rgba(231, 76, 60, 0.2);" onclick="showEndDayForm()">
                🏁 End Day / Conclude Route
            </button>

            <form id="end-day-form" action="<?= APP_URL ?>/driver/end_trip" method="POST" style="display: none; margin-top: 20px; border-top: 1px dashed var(--border); padding-top: 20px;" onsubmit="return validateEndDay(event)">
                <input type="hidden" name="delivery_id" value="<?= $delivery->id ?>">
                <input type="hidden" id="start-meter-val" value="<?= floatval($delivery->start_meter) ?>">
                <input type="hidden" id="expected-cash-val" value="<?= floatval($data['today_cash_collected']) ?>">

                <label class="form-label">Ending Odometer Reading (KM)</label>
                <input type="number" step="0.01" name="end_meter" id="end-meter-input" required class="form-input" placeholder="e.g. 12095.80" style="margin-bottom: 25px;">

                <div style="background: var(--app-bg); padding: 15px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 25px;">
                    <h4 style="margin: 0 0 12px; font-size: 14px; font-weight: 800; text-transform: uppercase; color: var(--primary);">💵 Cash Balancing Terminal</h4>
                    <p style="font-size: 12px; color: var(--text-muted); margin: 0 0 15px;">Enter counts to balance cash collections & credit collections.</p>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px;">
                        <span>Expected Cash Collected:</span>
                        <strong style="color: var(--text-dark);">Rs. <?= number_format($data['today_cash_collected'], 2) ?></strong>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <?php 
                        $denoms = [5000, 2000, 1000, 500, 100, 50, 20];
                        foreach ($denoms as $d):
                        ?>
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                                <span style="font-size: 13px; font-weight: 700; width: 80px;">Rs. <?= $d ?> x</span>
                                <input type="number" name="denom[<?= $d ?>]" class="form-input denom-input" data-val="<?= $d ?>" value="0" min="0" oninput="updateCashSum()" style="margin: 0; padding: 8px 12px; font-size: 14px; text-align: right; width: 100px;">
                            </div>
                        <?php endforeach; ?>
                        
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 4px;">
                            <span style="font-size: 13px; font-weight: 700; width: 80px;">Coins Total</span>
                            <input type="number" step="0.01" name="denom[coins]" class="form-input denom-input" data-val="1" id="coins-total" value="0.00" min="0" oninput="updateCashSum()" style="margin: 0; padding: 8px 12px; font-size: 14px; text-align: right; width: 100px;">
                        </div>
                    </div>

                    <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid var(--border);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 14px;">
                            <span>Total Entered Cash:</span>
                            <strong id="entered-cash-text" style="color: var(--text-dark);">Rs. 0.00</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px;">
                            <span>Difference:</span>
                            <strong id="cash-diff-text" style="color: var(--danger);">Rs. -<?= number_format($data['today_cash_collected'], 2) ?></strong>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="background: var(--success); box-shadow: 0 4px 12px rgba(46,204,113,0.2);">
                    Confirm Cash Balance & Conclude
                </button>
            </form>
        </div>

        <script>
            function showEndDayForm() {
                document.getElementById('btn-show-end-day').style.display = 'none';
                document.getElementById('end-day-form').style.display = 'block';
                updateCashSum();
            }

            function calculateEnteredCash() {
                var inputs = document.querySelectorAll('.denom-input');
                var total = 0.0;
                for (var i = 0; i < inputs.length; i++) {
                    var factor = parseFloat(inputs[i].getAttribute('data-val')) || 1;
                    var val = parseFloat(inputs[i].value) || 0;
                    total += (factor * val);
                }
                return total;
            }

            function updateCashSum() {
                var expected = parseFloat(document.getElementById('expected-cash-val').value) || 0;
                var entered = calculateEnteredCash();
                var diff = entered - expected;

                document.getElementById('entered-cash-text').innerText = 'Rs. ' + entered.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                
                var diffText = document.getElementById('cash-diff-text');
                diffText.innerText = 'Rs. ' + diff.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                
                if (Math.abs(diff) < 0.01) {
                    diffText.style.color = 'var(--success)';
                } else if (diff > 0) {
                    diffText.style.color = 'var(--primary)';
                } else {
                    diffText.style.color = 'var(--danger)';
                }
            }

            function validateEndDay(e) {
                var endMeter = parseFloat(document.getElementById('end-meter-input').value);
                var startMeter = parseFloat(document.getElementById('start-meter-val').value);
                if (isNaN(endMeter) || endMeter < startMeter) {
                    e.preventDefault();
                    alert("Ending Odometer Reading cannot be less than starting odometer (" + startMeter + " KM).");
                    return false;
                }

                var expectedCash = parseFloat(document.getElementById('expected-cash-val').value) || 0;
                var enteredCash = calculateEnteredCash();
                var diff = Math.abs(enteredCash - expectedCash);

                if (diff > 0.01) {
                    e.preventDefault();
                    alert("Cash count is not balanced! Expected Rs. " + expectedCash.toFixed(2) + " but entered Rs. " + enteredCash.toFixed(2) + " (Diff: Rs. " + (enteredCash - expectedCash).toFixed(2) + "). Please balance the cash collections before concluding the route.");
                    return false;
                }
                return confirm("Are you sure you want to end this trip and submit the daily cash balancing?");
            }
        </script>

    <?php elseif ($delivery->status === 'Completed' || $delivery->status === 'Finalized'): ?>
        <!-- 4. COMPLETED / FINALIZED REPORT STATE -->
        <div class="card" style="padding: 20px; text-align: center; border-top: 5px solid var(--success);">
            <div style="font-size: 48px; margin-bottom: 10px;">🏆</div>
            <span class="badge badge-success" style="margin-bottom: 15px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">
                Route <?= htmlspecialchars($delivery->status) ?>
            </span>
            <h2 style="margin: 0 0 10px; font-size: 22px; font-weight: 800;"><?= htmlspecialchars($delivery->route_name) ?></h2>
            <p style="color: var(--text-muted); font-size: 13px; margin: 0 0 20px;">
                This delivery route has been successfully completed and settled. Below is the final summary report.
            </p>

            <div style="text-align: left; background: var(--app-bg); border-radius: 12px; padding: 15px; margin-bottom: 25px; border: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px;">
                    <span style="color: var(--text-muted);">Vehicle Number:</span>
                    <strong><?= htmlspecialchars($delivery->vehicle_number) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px;">
                    <span style="color: var(--text-muted);">Assigned Driver:</span>
                    <strong><?= htmlspecialchars($delivery->driver_name) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px;">
                    <span style="color: var(--text-muted);">Start Odometer:</span>
                    <strong><?= htmlspecialchars($delivery->start_meter) ?> KM</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 14px;">
                    <span style="color: var(--text-muted);">End Odometer:</span>
                    <strong><?= htmlspecialchars($delivery->end_meter) ?> KM</strong>
                </div>
            </div>

            <h3 style="text-align: left; font-size: 14px; font-weight: 800; text-transform: uppercase; margin: 20px 0 10px; color: var(--text-muted);">Shops & Deliveries</h3>
            <div style="display: flex; flex-direction: column; gap: 10px; text-align: left; margin-bottom: 25px;">
                <?php foreach ($data['shops'] as $shop): ?>
                    <div class="card" style="margin: 0; padding: 12px 15px; border: 1px solid rgba(46,204,113,0.3); background: rgba(46,204,113,0.03); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="font-size: 14px; color: var(--text-dark);"><?= htmlspecialchars($shop->name) ?></strong>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">📍 <?= htmlspecialchars($shop->address ?: 'No Address') ?></div>
                        </div>
                        <span class="badge badge-success" style="font-size: 9px; padding: 2px 6px;">Delivered</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php endif; ?>

<?php endif; ?>
