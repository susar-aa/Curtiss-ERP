<?php
$collections = $data['collections'] ?? [];
$totals = $data['totals'] ?? [];
$page = $data['page'] ?? 1;
$totalPages = $data['totalPages'] ?? 1;
$totalCount = $data['totalCount'] ?? 0;
?>
<style>
    .pending-gl-header { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(251, 191, 36, 0.2); }
    .pending-gl-header h1 { margin: 0 0 10px 0; font-size: 28px; }
    .pending-gl-header p { margin: 0; font-size: 14px; opacity: 0.95; }

    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 20px; border-radius: 8px; border-left: 4px solid; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .stat-card.cash { border-left-color: #10b981; }
    .stat-card.cheque { border-left-color: #f97316; }
    .stat-card.bank { border-left-color: #3b82f6; }
    .stat-card.total { border-left-color: #8b5cf6; background: linear-gradient(135deg, rgba(139, 92, 246, 0.05) 0%, rgba(168, 85, 247, 0.05) 100%); }

    .stat-label { font-size: 12px; font-weight: bold; color: #666; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px; }
    .stat-value { font-size: 24px; font-weight: bold; color: #333; font-family: monospace; }
    .stat-count { font-size: 13px; color: #999; margin-top: 8px; }

    .collections-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .collections-table thead { background: #f3f4f6; }
    .collections-table th { padding: 15px; text-align: left; font-weight: bold; font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; }
    .collections-table td { padding: 15px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
    .collections-table tbody tr:hover { background: #f9fafb; }

    .method-badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; }
    .method-cash { background: #dcfce7; color: #166534; }
    .method-bank { background: #dbeafe; color: #1e40af; }
    .method-cheque { background: #fed7aa; color: #92400e; }

    .status-badge { display: inline-block; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    .status-pending { background: #fef3c7; color: #b45309; }
    .status-finalized { background: #d1fae5; color: #065f46; }
    .status-rejected { background: #fee2e2; color: #991b1b; }

    .action-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
    .btn-finalize { background: #10b981; color: white; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; font-size: 12px; font-weight: bold; transition: all 0.2s; }
    .btn-finalize:hover { background: #059669; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
    .btn-reject { background: #ef4444; color: white; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; font-size: 12px; font-weight: bold; transition: all 0.2s; }
    .btn-reject:hover { background: #dc2626; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); }
    .btn-view { background: #0066cc; color: white; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; font-size: 12px; font-weight: bold; text-decoration: none; display: inline-block; transition: all 0.2s; }
    .btn-view:hover { background: #0052a3; box-shadow: 0 4px 12px rgba(0, 102, 204, 0.3); }

    .empty-state { text-align: center; padding: 40px; color: #999; }
    .empty-state h3 { margin: 0; font-size: 18px; color: #666; }
    .empty-state p { margin: 10px 0 0 0; font-size: 14px; }

    .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 30px; align-items: center; }
    .page-link { padding: 8px 12px; border-radius: 4px; background: #f0f0f0; color: #333; text-decoration: none; font-size: 14px; border: 1px solid #ddd; transition: all 0.2s; }
    .page-link:hover { background: #e0e0e0; }
    .page-link.active { background: #0066cc; color: white; }
</style>

<div class="pending-gl-header">
    <h1>📄 Pending GL Collections</h1>
    <p>Review and finalize temporary payment collections awaiting GL posting</p>
</div>

<?php if(isset($_SESSION['success'])): ?>
<div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #10b981;">
    ✓ <?= htmlspecialchars($_SESSION['success']) ?>
</div>
<?php unset($_SESSION['success']); endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ef4444;">
    ✗ <?= htmlspecialchars($_SESSION['error']) ?>
</div>
<?php unset($_SESSION['error']); endif; ?>

<!-- Summary Stats -->
<div class="stats-grid">
    <div class="stat-card cash">
        <div class="stat-label">💵 Cash Collected</div>
        <div class="stat-value">Rs <?= number_format($totals['cash'] ?? 0, 2) ?></div>
    </div>
    <div class="stat-card bank">
        <div class="stat-label">🏛️ Bank Transfers</div>
        <div class="stat-value">Rs <?= number_format($totals['bank'] ?? 0, 2) ?></div>
    </div>
    <div class="stat-card cheque">
        <div class="stat-label">✍️ Cheques Received</div>
        <div class="stat-value">Rs <?= number_format($totals['cheque'] ?? 0, 2) ?></div>
    </div>
    <div class="stat-card total">
        <div class="stat-label">Total Pending Value</div>
        <div class="stat-value">Rs <?= number_format($totals['total'] ?? 0, 2) ?></div>
        <div class="stat-count"><?= $totalCount ?> collections pending approval</div>
    </div>
</div>

<!-- Collections Table -->
<?php if(!empty($collections)): ?>
<table class="collections-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Customer</th>
            <th>Route</th>
            <th>Method</th>
            <th>Amount</th>
            <th>Details</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($collections as $item): ?>
        <tr>
            <td><?= date('M d, Y H:i', strtotime($item->created_at)) ?></td>
            <td>
                <strong><?= htmlspecialchars($item->customer_name ?? 'Unknown') ?></strong><br>
                <small style="color: #999;">By: <?= htmlspecialchars($item->username ?? 'N/A') ?></small>
            </td>
            <td><?= htmlspecialchars($item->route_name ?? 'N/A') ?></td>
            <td>
                <span class="method-badge method-<?= strtolower($item->payment_method) ?>">
                    <?= htmlspecialchars($item->payment_method) ?>
                </span>
            </td>
            <td style="font-weight: bold; font-family: monospace;">Rs <?= number_format($item->amount, 2) ?></td>
            <td>
                <?php if($item->payment_method === 'Cheque'): ?>
                    <small>
                        Cheque #: <?= htmlspecialchars($item->cheque_number ?? 'N/A') ?><br>
                        <?php if(!empty($item->cheque_date)): ?>
                        Clearing: <?= htmlspecialchars($item->cheque_date) ?>
                        <?php endif; ?>
                    </small>
                <?php elseif($item->payment_method === 'Bank Transfer'): ?>
                    <small>Bank: <?= htmlspecialchars($item->bank_name ?? 'N/A') ?></small>
                <?php endif; ?>
            </td>
            <td>
                <div class="action-buttons">
                    <a href="<?= APP_URL ?>/accounting/view_collection/<?= $item->id ?>" class="btn-view">View</a>
                    <form method="POST" action="<?= APP_URL ?>/accounting/finalize_collection" style="display: inline;">
                        <input type="hidden" name="collection_id" value="<?= $item->id ?>">
                        <input type="hidden" name="notes" value="">
                        <button type="submit" class="btn-finalize" onclick="return confirm('Approve this collection?')">Approve</button>
                    </form>
                    <form method="POST" action="<?= APP_URL ?>/accounting/reject_collection" style="display: inline;">
                        <input type="hidden" name="collection_id" value="<?= $item->id ?>">
                        <input type="hidden" name="reason" value="">
                        <button type="submit" class="btn-reject" onclick="return confirm('Reject this collection?')">Reject</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Pagination -->
<?php if($totalPages > 1): ?>
<div class="pagination">
    <?php if($page > 1): ?>
        <a href="?page=1" class="page-link">« First</a>
        <a href="?page=<?= $page - 1 ?>" class="page-link">‹ Previous</a>
    <?php endif; ?>

    <?php 
    $start = max(1, $page - 2);
    $end = min($totalPages, $page + 2);
    if ($start > 1) echo '<span>...</span>';
    for ($i = $start; $i <= $end; $i++):
    ?>
        <a href="?page=<?= $i ?>" class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; if ($end < $totalPages) echo '<span>...</span>'; ?>

    <?php if($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>" class="page-link">Next ›</a>
        <a href="?page=<?= $totalPages ?>" class="page-link">Last »</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php else: ?>
<div class="empty-state">
    <h3>No Pending Collections</h3>
    <p>All pending GL collections have been processed or there are no pending entries.</p>
</div>
<?php endif; ?>
