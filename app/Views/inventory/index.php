<?php
?>
<style>
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn { padding: 8px 16px; background: #0066cc; color: #fff; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px;}
    .btn:hover { background: #005bb5; }
    .btn-outline { background: transparent; border: 1px solid #0066cc; color: #0066cc; }
    .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--mac-border); }
    .data-table th { background-color: rgba(0,0,0,0.02); font-weight: 600; font-size: 13px; }
    .badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; }
    .badge-inventory { background: #e3f2fd; color: #1565c0; }
    .badge-service { background: #f3e5f5; color: #6a1b9a; }
    .badge-category { background: #f5f5f5; color: #555; border: 1px solid #ddd; }
    
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
    .modal-content { background: var(--mac-bg); backdrop-filter: blur(20px); padding: 30px; border-radius: 12px; width: 550px; border: 1px solid var(--mac-border); max-height: 90vh; overflow-y: auto;}
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: transparent; color: var(--text-main); box-sizing: border-box;}
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

    .alert-box { background: #ffebee; border-left: 4px solid #c62828; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
    .alert-box h4 { margin: 0 0 5px 0; color: #c62828; }
</style>

<div class="card">
    <div class="header-actions">
        <h2>Products & Inventory</h2>
        <div>
            <button class="btn btn-outline" onclick="document.getElementById('catModal').style.display='flex'">+ Manage Categories</button>
            <button class="btn" onclick="document.getElementById('itemModal').style.display='flex'">+ Add New Item</button>
        </div>
    </div>

    <?php if(!empty($data['error'])): ?>
        <div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:4px; margin-bottom:15px;"><?= $data['error'] ?></div>
    <?php endif; ?>
    <?php if(!empty($data['success'])): ?>
        <div style="padding: 10px; background:#e8f5e9; color:#2e7d32; border-radius:4px; margin-bottom:15px;"><?= $data['success'] ?></div>
    <?php endif; ?>

    <?php if(count($data['low_stock_items']) > 0): ?>
        <div class="alert-box">
            <h4>⚠ Low Stock Alert</h4>
            <p style="margin:0; font-size:13px;">You have <strong><?= count($data['low_stock_items']) ?></strong> item(s) running low. Please review stock levels and issue Purchase Orders.</p>
        </div>
    <?php endif; ?>

    <table class="data-table">
        <thead>
            <tr>
                <th>Item / SKU</th>
                <th>Category</th>
                <th>Type</th>
                <th style="text-align: right;">Sales Price (Rs:)</th>
                <th style="text-align: center;">In Stock</th>
                <th style="text-align: center;">Min Stock</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($data['items'])): ?>
            <tr><td colspan="6" style="text-align: center; color: #888;">No items found. Click add to create your catalog.</td></tr>
            <?php else: foreach($data['items'] as $item): ?>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($item->name) ?></strong><br>
                    <span style="font-size: 11px; color: #666;"><?= htmlspecialchars($item->item_code) ?></span>
                </td>
                <td>
                    <?php if($item->category_name): ?>
                        <span class="badge badge-category"><?= htmlspecialchars($item->category_name) ?></span>
                    <?php else: ?>
                        <span style="color:#aaa; font-size:12px;">Uncategorized</span>
                    <?php endif; ?>
                </td>
                <td><span class="badge badge-<?= strtolower($item->type) ?>"><?= $item->type ?></span></td>
                <td style="text-align: right; font-weight:bold;"><?= number_format($item->price, 2) ?></td>
                
                <?php 
                    $isLowStock = ($item->type == 'Inventory' && $item->quantity_on_hand <= $item->minimum_stock_level);
                ?>
                <td style="text-align: center;">
                    <?php if($item->type == 'Service'): ?>
                        <span style="color:#aaa;">--</span>
                    <?php else: ?>
                        <span style="font-weight:bold; color: <?= $isLowStock ? '#c62828' : '#2e7d32' ?>; <?= $isLowStock ? 'background:#ffebee; padding:4px 8px; border-radius:4px;' : '' ?>">
                            <?= $item->quantity_on_hand ?>
                        </span>
                    <?php endif; ?>
                </td>
                <td style="text-align: center; color: #888; font-size:13px;">
                    <?= $item->type == 'Inventory' ? $item->minimum_stock_level : '--' ?>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Category Modal -->
<div class="modal" id="catModal">
    <div class="modal-content" style="width: 400px;">
        <h3 style="margin-top:0;">Add Product Category</h3>
        <form action="<?= APP_URL ?>/inventory" method="POST">
            <input type="hidden" name="action" value="add_category">
            <div class="form-group">
                <label>Category Name *</label>
                <input type="text" name="category_name" class="form-control" placeholder="e.g. Hardware, Services, Subscriptions" required>
            </div>
            <div class="form-group">
                <label>Description (Optional)</label>
                <input type="text" name="description" class="form-control">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('catModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn">Save Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal" id="itemModal">
    <div class="modal-content">
        <h3 style="margin-top:0;">Add Product / Service</h3>
        <form action="<?= APP_URL ?>/inventory" method="POST">
            <input type="hidden" name="action" value="add_item">
            
            <div class="form-group">
                <label>Item Name *</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            
            <div class="grid-2">
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" class="form-control">
                        <option value="">-- No Category --</option>
                        <?php foreach($data['categories'] as $cat): ?>
                            <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>SKU / Item Code</label>
                    <input type="text" name="item_code" class="form-control" placeholder="e.g. SRV-01">
                </div>
            </div>

            <div class="form-group">
                <label>Item Type</label>
                <select name="type" class="form-control" id="itemTypeSelect" onchange="toggleInventory(this.value)">
                    <option value="Inventory">Inventory (Track Stock)</option>
                    <option value="Service">Service (No tracking)</option>
                </select>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>Sales Price (Rs:)</label>
                    <input type="number" name="price" step="0.01" min="0" class="form-control" value="0.00">
                </div>
                <div class="form-group">
                    <label>Cost (Rs:)</label>
                    <input type="number" name="cost" step="0.01" min="0" class="form-control" value="0.00">
                </div>
            </div>

            <div class="grid-2" id="qtyDiv">
                <div class="form-group">
                    <label>Initial Quantity on Hand</label>
                    <input type="number" name="qty" step="1" min="0" class="form-control" value="0">
                </div>
                <div class="form-group">
                    <label style="color:#c62828;">Minimum Alert Level</label>
                    <input type="number" name="min_stock" step="1" min="0" class="form-control" value="5">
                </div>
            </div>

            <div class="form-group" style="background: rgba(0,102,204,0.05); padding: 10px; border-radius: 4px; border: 1px solid rgba(0,102,204,0.2); margin-top:10px;">
                <label style="color:#0066cc;">Income Account (For Sales) *</label>
                <select name="income_account_id" class="form-control" required>
                    <option value="">Select Revenue Account...</option>
                    <?php foreach($data['revenues'] as $acc): ?>
                        <option value="<?= $acc->id ?>"><?= $acc->account_code ?> - <?= htmlspecialchars($acc->account_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('itemModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn">Save Item</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleInventory(type) {
        const qtyDiv = document.getElementById('qtyDiv');
        if (type === 'Service') {
            qtyDiv.style.display = 'none';
        } else {
            qtyDiv.style.display = 'grid';
        }
    }
</script>