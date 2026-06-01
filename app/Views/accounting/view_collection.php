<?php
$c = $data['collection'] ?? null;

if (!$c) {
    echo '<p>Collection not found</p>';
    exit;
}
?>
<style>
    .detail-header { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; }
    .detail-header h1 { margin: 0; font-size: 28px; }
    .detail-header p { margin: 10px 0 0 0; opacity: 0.95; }

    .detail-container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }

    .detail-section { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #e5e7eb; }
    .detail-section:last-child { border-bottom: none; margin-bottom: 0; }
    .detail-section h2 { margin: 0 0 15px 0; font-size: 16px; font-weight: bold; color: #333; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; color: #666; }

    .detail-row { display: grid; grid-template-columns: 200px 1fr; gap: 15px; margin-bottom: 12px; align-items: start; }
    .detail-label { font-weight: bold; color: #666; font-size: 13px; }
    .detail-value { color: #333; font-size: 14px; }
    .detail-value.highlight { font-weight: bold; color: #0066cc; font-family: monospace; font-size: 16px; }

    .badge { display: inline-block; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    .badge-cash { background: #dcfce7; color: #166534; }
    .badge-bank { background: #dbeafe; color: #1e40af; }
    .badge-cheque { background: #fed7aa; color: #92400e; }
    .badge-pending { background: #fef3c7; color: #b45309; }

    .action-panel { display: flex; gap: 15px; margin-top: 30px; }
    .btn { padding: 12px 20px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: bold; transition: all 0.2s; }
    .btn-approve { background: #10b981; color: white; }
    .btn-approve:hover { background: #059669; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
    .btn-reject { background: #ef4444; color: white; }
    .btn-reject:hover { background: #dc2626; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); }
    .btn-back { background: #f0f0f0; color: #333; border: 1px solid #ddd; }
    .btn-back:hover { background: #e0e0e0; }

    .notes-textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-family: Arial, sans-serif; font-size: 13px; resize: vertical; min-height: 80px; }
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; margin-bottom: 8px; font-weight: bold; font-size: 13px; color: #333; }

    .modal-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000; }
    .modal { background: white; padding: 30px; border-radius: 12px; max-width: 400px; width: 90%; }
    .modal-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333; }
    .modal-buttons { display: flex; gap: 10px; margin-top: 20px; }
</style>

<div class="detail-header">
    <h1>Collection Detail</h1>
    <p>Review and finalize this pending GL collection</p>
</div>

<div class="detail-container">
    <!-- Collection Info -->
    <div class="detail-section">
        <h2>Collection Information</h2>
        <div class="detail-row">
            <div class="detail-label">Status</div>
            <div class="detail-value">
                <span class="badge badge-<?= strtolower($c->status) ?>">
                    <?= htmlspecialchars($c->status ?? 'Unknown') ?>
                </span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Payment Method</div>
            <div class="detail-value">
                <span class="badge badge-<?= strtolower($c->payment_method) ?>">
                    <?= htmlspecialchars($c->payment_method ?? 'Unknown') ?>
                </span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Amount</div>
            <div class="detail-value highlight">Rs <?= number_format($c->amount, 2) ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Recorded Date</div>
            <div class="detail-value"><?= date('M d, Y H:i A', strtotime($c->created_at)) ?></div>
        </div>
    </div>

    <!-- Customer Info -->
    <div class="detail-section">
        <h2>Customer Details</h2>
        <div class="detail-row">
            <div class="detail-label">Customer Name</div>
            <div class="detail-value"><?= htmlspecialchars($c->customer_name ?? 'Unknown') ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Phone</div>
            <div class="detail-value"><?= htmlspecialchars($c->phone ?? 'N/A') ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Address</div>
            <div class="detail-value"><?= htmlspecialchars($c->address ?? 'N/A') ?></div>
        </div>
    </div>

    <!-- Route Info -->
    <div class="detail-section">
        <h2>Route Information</h2>
        <div class="detail-row">
            <div class="detail-label">Route</div>
            <div class="detail-value"><?= htmlspecialchars($c->route_name ?? 'N/A') ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Representative</div>
            <div class="detail-value"><?= htmlspecialchars(($c->first_name ?? '') . ' ' . ($c->last_name ?? '')) ?> (<?= htmlspecialchars($c->username ?? 'N/A') ?>)</div>
        </div>
    </div>

    <!-- Payment Details -->
    <?php if($c->payment_method === 'Cheque' && !empty($c->cheque_number)): ?>
    <div class="detail-section">
        <h2>Cheque Details</h2>
        <div class="detail-row">
            <div class="detail-label">Cheque Number</div>
            <div class="detail-value"><?= htmlspecialchars($c->cheque_number) ?></div>
        </div>
        <?php if(!empty($c->bank_name)): ?>
        <div class="detail-row">
            <div class="detail-label">Bank Name</div>
            <div class="detail-value"><?= htmlspecialchars($c->bank_name) ?></div>
        </div>
        <?php endif; ?>
        <?php if(!empty($c->cheque_date)): ?>
        <div class="detail-row">
            <div class="detail-label">Clearing Date</div>
            <div class="detail-value"><?= htmlspecialchars($c->cheque_date) ?></div>
        </div>
        <?php endif; ?>
    </div>
    <?php elseif($c->payment_method === 'Bank Transfer' && !empty($c->bank_name)): ?>
    <div class="detail-section">
        <h2>Bank Transfer Details</h2>
        <div class="detail-row">
            <div class="detail-label">Bank</div>
            <div class="detail-value"><?= htmlspecialchars($c->bank_name) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Action Panel -->
    <?php if($c->status === 'Pending'): ?>
    <div class="detail-section" style="border-bottom: none;">
        <h2 style="margin-bottom: 20px;">Approve or Reject</h2>

        <!-- Approve Form -->
        <form method="POST" action="<?= APP_URL ?>/accounting/finalize_collection" id="approveForm">
            <input type="hidden" name="collection_id" value="<?= $c->id ?>">
            <div class="form-group">
                <label class="form-label">Approval Notes (Optional)</label>
                <textarea name="notes" class="notes-textarea" placeholder="Add any notes for this approval..."></textarea>
            </div>
            <div class="action-panel">
                <button type="submit" class="btn btn-approve">✓ Approve Collection</button>
                <button type="button" onclick="document.getElementById('rejectModal').style.display='flex'" class="btn btn-reject">✗ Reject Collection</button>
                <a href="<?= APP_URL ?>/accounting/pending_collections" class="btn btn-back">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Rejection Modal -->
    <div id="rejectModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-title">Reject Collection</div>
            <form method="POST" action="<?= APP_URL ?>/accounting/reject_collection">
                <input type="hidden" name="collection_id" value="<?= $c->id ?>">
                <div class="form-group">
                    <label class="form-label">Rejection Reason</label>
                    <textarea name="reason" class="notes-textarea" required placeholder="Why are you rejecting this collection?"></textarea>
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn btn-reject" style="flex: 1;">Confirm Rejection</button>
                    <button type="button" onclick="document.getElementById('rejectModal').style.display='none'" class="btn btn-back" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="detail-section" style="background: #f0f0f0; padding: 15px; border-radius: 6px; border: none;">
        <p style="margin: 0; color: #666;">This collection has already been <?= strtolower($c->status) ?>.</p>
        <a href="<?= APP_URL ?>/accounting/pending_collections" class="btn btn-back" style="margin-top: 15px;">Back to List</a>
    </div>
    <?php endif; ?>
</div>

<script>
    document.getElementById('rejectModal').addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
</script>
