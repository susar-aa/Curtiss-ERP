<?php
?>
<style>
    .report-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
    .report-card { background: #fff; padding: 25px; border-radius: 8px; border: 1px solid var(--mac-border); text-align: center; display: flex; flex-direction: column; transition: transform 0.2s; }
    @media (prefers-color-scheme: dark) { .report-card { background: rgba(255,255,255,0.02); } }
    .report-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
    .report-icon { font-size: 40px; margin-bottom: 15px; }
    .report-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
    .report-desc { color: #666; font-size: 13px; margin-bottom: 20px; flex-grow: 1; }
    .btn { padding: 10px 20px; background: #0066cc; color: #fff; text-decoration: none; border-radius: 4px; font-weight: 500; display: inline-block; }
</style>

<div class="header-actions">
    <h2>Financial Reports</h2>
    <p style="color:#666; margin-top:0;">Generate real-time statements from your accounting ledger.</p>
</div>

<div class="report-grid">
    <div class="report-card">
        <div class="report-icon">📊</div>
        <div class="report-title">Profit & Loss</div>
        <div class="report-desc">Shows your revenue, expenses, and net profit over time. The most important report for seeing if you are making money.</div>
        <a href="<?= APP_URL ?>/report/profit_loss" class="btn" target="_blank">View Report</a>
    </div>

    <div class="report-card">
        <div class="report-icon">⚖️</div>
        <div class="report-title">Balance Sheet</div>
        <div class="report-desc">A snapshot of your business's financial health. Displays your Assets, Liabilities, and Owner's Equity.</div>
        <a href="<?= APP_URL ?>/report/balance_sheet" class="btn" target="_blank">View Report</a>
    </div>

    <div class="report-card">
        <div class="report-icon">💵</div>
        <div class="report-title">Statement of Cash Flows</div>
        <div class="report-desc">Tracks how cash moves in and out of your business through Operating, Investing, and Financing activities.</div>
        <a href="<?= APP_URL ?>/report/cash_flow" class="btn" target="_blank">View Report</a>
    </div>

    <div class="report-card">
        <div class="report-icon">📝</div>
        <div class="report-title">Trial Balance</div>
        <div class="report-desc">The accountant's worksheet. Proves that your total Debits exactly match your total Credits across all ledgers.</div>
        <a href="<?= APP_URL ?>/report/trial_balance" class="btn" target="_blank">View Report</a>
    </div>

    <!-- NEW A/R AGING REPORT -->
    <div class="report-card">
        <div class="report-icon">⏳</div>
        <div class="report-title">A/R Aging Summary</div>
        <div class="report-desc">See exactly which customers owe you money, and how many days their invoices are past due.</div>
        <a href="<?= APP_URL ?>/report/ar_aging" class="btn" target="_blank">View Report</a>
    </div>
</div>