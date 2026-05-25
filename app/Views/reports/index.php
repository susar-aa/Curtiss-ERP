<?php
$ds = $data['default_start'] ?? date('Y-m-d', strtotime('-30 days'));
$de = $data['default_end'] ?? date('Y-m-d');

function reportLink($slug, $dated = true) {
    global $ds, $de;
    $url = APP_URL . '/report/' . $slug;
    if ($dated) {
        $url .= '?start_date=' . urlencode($ds) . '&end_date=' . urlencode($de);
    }
    return $url;
}
?>
<style>
    .hub-header { margin-bottom: 20px; }
    .hub-header p { color: #666; margin: 4px 0 0; }
    .period-bar { background: #e8f0fe; border: 1px solid #c5d9f7; border-radius: 8px; padding: 16px 20px; margin-bottom: 28px; display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
    .period-bar label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #555; display: block; margin-bottom: 4px; }
    .period-bar input { padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; }
    .period-bar small { color: #666; font-size: 12px; align-self: center; }
    .report-section { margin-bottom: 32px; }
    .report-section h3 { font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; color: #0066cc; margin: 0 0 14px; padding-bottom: 8px; border-bottom: 2px solid #e0e0e0; }
    .report-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
    .report-card { background: #fff; padding: 22px; border-radius: 8px; border: 1px solid var(--mac-border); display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s; }
    @media (prefers-color-scheme: dark) { .report-card { background: rgba(255,255,255,0.03); } }
    .report-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
    .report-icon { font-size: 32px; margin-bottom: 10px; }
    .report-title { font-size: 16px; font-weight: bold; margin-bottom: 8px; }
    .report-desc { color: #666; font-size: 12px; line-height: 1.45; margin-bottom: 16px; flex-grow: 1; }
    .report-card .btn { padding: 9px 16px; background: #0066cc; color: #fff; text-decoration: none; border-radius: 4px; font-weight: 600; font-size: 12px; text-align: center; }
    .report-card .btn:hover { background: #0055aa; }
    .tag-dated { display: inline-block; font-size: 10px; background: #e3f2fd; color: #1565c0; padding: 2px 6px; border-radius: 3px; margin-left: 6px; font-weight: 600; }
</style>

<div class="hub-header">
    <h2>Financial Reports Hub</h2>
    <p>Real-time statements from your ledger, sales, purchases, and collections.</p>
</div>

<form class="period-bar" id="periodForm" onsubmit="return false;">
    <div>
        <label>Default period — From</label>
        <input type="date" id="hubStart" value="<?= htmlspecialchars($ds) ?>">
    </div>
    <div>
        <label>To</label>
        <input type="date" id="hubEnd" value="<?= htmlspecialchars($de) ?>">
    </div>
    <small>Date-filtered reports open with this range. Change dates on each report to refine.</small>
</form>

<div class="report-section">
    <h3>Financial statements</h3>
    <div class="report-grid">
        <div class="report-card">
            <div class="report-icon">📊</div>
            <div class="report-title">Profit & Loss <span class="tag-dated">Ledger</span></div>
            <div class="report-desc">Current ledger balances for all revenue and expense accounts.</div>
            <a href="<?= APP_URL ?>/report/profit_loss" class="btn" target="_blank">View Report</a>
        </div>
        <div class="report-card">
            <div class="report-icon">📅</div>
            <div class="report-title">P&amp;L by Period</div>
            <div class="report-desc">Income and expenses posted in the selected date range (journal-based).</div>
            <a href="<?= reportLink('profit_loss_period') ?>" class="btn" target="_blank" data-dated="1">View Report</a>
        </div>
        <div class="report-card">
            <div class="report-icon">⚖️</div>
            <div class="report-title">Balance Sheet</div>
            <div class="report-desc">Assets, liabilities, and equity including current-year net income.</div>
            <a href="<?= APP_URL ?>/report/balance_sheet" class="btn" target="_blank">View Report</a>
        </div>
        <div class="report-card">
            <div class="report-icon">💵</div>
            <div class="report-title">Cash Flow</div>
            <div class="report-desc">Operating, investing, and financing cash movement summary.</div>
            <a href="<?= APP_URL ?>/report/cash_flow" class="btn" target="_blank">View Report</a>
        </div>
        <div class="report-card">
            <div class="report-icon">📝</div>
            <div class="report-title">Trial Balance</div>
            <div class="report-desc">All accounts with debit and credit totals — must balance.</div>
            <a href="<?= APP_URL ?>/report/trial_balance" class="btn" target="_blank">View Report</a>
        </div>
        <div class="report-card">
            <div class="report-icon">📒</div>
            <div class="report-title">General Ledger</div>
            <div class="report-desc">Every journal line by date, account, debit, and credit.</div>
            <a href="<?= reportLink('general_ledger') ?>" class="btn" target="_blank" data-dated="1">View Report</a>
        </div>
    </div>
</div>

<div class="report-section">
    <h3>Sales &amp; receivables</h3>
    <div class="report-grid">
        <div class="report-card">
            <div class="report-icon">🛒</div>
            <div class="report-title">Sales Summary</div>
            <div class="report-desc">Invoice counts, gross sales, tax, paid vs outstanding — daily breakdown.</div>
            <a href="<?= reportLink('sales_summary') ?>" class="btn" target="_blank" data-dated="1">View Report</a>
        </div>
        <div class="report-card">
            <div class="report-icon">👥</div>
            <div class="report-title">Sales by Customer</div>
            <div class="report-desc">Per-customer sales, payments, and outstanding balances.</div>
            <a href="<?= reportLink('sales_by_customer') ?>" class="btn" target="_blank" data-dated="1">View Report</a>
        </div>
        <div class="report-card">
            <div class="report-icon">📦</div>
            <div class="report-title">Sales by Product</div>
            <div class="report-desc">Quantity, revenue, cost, profit, and margin by item.</div>
            <a href="<?= reportLink('sales_by_product') ?>" class="btn" target="_blank" data-dated="1">View Report</a>
        </div>
        <div class="report-card">
            <div class="report-icon">🚗</div>
            <div class="report-title">Sales by Rep Route</div>
            <div class="report-desc">Territory route performance and invoice totals per rep day.</div>
            <a href="<?= reportLink('sales_by_rep') ?>" class="btn" target="_blank" data-dated="1">View Report</a>
        </div>
        <div class="report-card">
            <div class="report-icon">⏳</div>
            <div class="report-title">A/R Aging</div>
            <div class="report-desc">Unpaid invoices by customer and aging bucket with invoice detail.</div>
            <a href="<?= APP_URL ?>/report/ar_aging" class="btn" target="_blank">View Report</a>
        </div>
        <div class="report-card">
            <div class="report-icon">💳</div>
            <div class="report-title">Collections</div>
            <div class="report-desc">Customer payments by method with full transaction detail.</div>
            <a href="<?= reportLink('collections') ?>" class="btn" target="_blank" data-dated="1">View Report</a>
        </div>
        <div class="report-card">
            <div class="report-icon">🧾</div>
            <div class="report-title">Tax Summary</div>
            <div class="report-desc">Daily subtotal, discounts, tax, and grand totals from invoices.</div>
            <a href="<?= reportLink('tax_summary') ?>" class="btn" target="_blank" data-dated="1">View Report</a>
        </div>
    </div>
</div>

<div class="report-section">
    <h3>Purchases &amp; inventory</h3>
    <div class="report-grid">
        <div class="report-card">
            <div class="report-icon">🛍️</div>
            <div class="report-title">Purchases &amp; GRN</div>
            <div class="report-desc">PO and goods-received values by vendor for the period.</div>
            <a href="<?= reportLink('purchases') ?>" class="btn" target="_blank" data-dated="1">View Report</a>
        </div>
        <div class="report-card">
            <div class="report-icon">🗄️</div>
            <div class="report-title">Inventory Valuation</div>
            <div class="report-desc">Stock on hand valued at cost and at retail selling price.</div>
            <a href="<?= APP_URL ?>/report/inventory_valuation" class="btn" target="_blank">View Report</a>
        </div>
        <div class="report-card">
            <div class="report-icon">📈</div>
            <div class="report-title">FIFO Profit &amp; Margin</div>
            <div class="report-desc">Line-level sales profit using cost at time of sale.</div>
            <a href="<?= reportLink('fifo_profit') ?>" class="btn" target="_blank" data-dated="1">View Report</a>
        </div>
    </div>
</div>

<script>
(function() {
    const start = document.getElementById('hubStart');
    const end = document.getElementById('hubEnd');
    function refreshLinks() {
        const s = start.value, e = end.value;
        document.querySelectorAll('a[data-dated="1"]').forEach(a => {
            const base = a.href.split('?')[0];
            a.href = base + '?start_date=' + encodeURIComponent(s) + '&end_date=' + encodeURIComponent(e);
        });
    }
    start.addEventListener('change', refreshLinks);
    end.addEventListener('change', refreshLinks);
})();
</script>
