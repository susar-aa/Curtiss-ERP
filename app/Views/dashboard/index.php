<?php
/* STREAMING_CHUNK:Building the classic visual flowchart dashboard... */
?>
<style>
    /* STREAMING_CHUNK:Styling the layout containers... */
    .workflow-container {
        display: grid;
        grid-template-columns: 7fr 3fr;
        gap: 20px;
        min-height: 80vh;
        background-color: #f4f5f7;
    }

    .panel-group {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .flow-panel {
        background-color: #eaf1f8; /* Light blue QuickBooks style background */
        border: 1px solid #cdd8e4;
        border-radius: 6px;
        display: flex;
        position: relative;
        min-height: 150px;
        box-shadow: inset 0 0 20px rgba(255,255,255,0.5);
    }

    @media (prefers-color-scheme: dark) {
        .flow-panel { background-color: #1e293b; border-color: #334155; }
        .workflow-container { background-color: transparent; }
    }

    /* STREAMING_CHUNK:Styling the vertical side labels... */
    .panel-tab {
        background-color: #d1d9e6;
        color: #555;
        writing-mode: vertical-rl;
        text-orientation: mixed;
        transform: rotate(180deg);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px 5px;
        font-size: 11px;
        font-weight: bold;
        text-transform: uppercase;
        border-right: 1px solid #cdd8e4;
        border-radius: 6px 0 0 6px;
        letter-spacing: 1px;
    }

    .panel-tab-top {
        background-color: transparent;
        color: #555;
        font-size: 11px;
        font-weight: bold;
        text-transform: uppercase;
        padding: 8px 15px;
        position: absolute;
        top: 0;
        left: 0;
    }

    @media (prefers-color-scheme: dark) {
        .panel-tab { background-color: #334155; color: #cbd5e1; border-color: #0f172a; }
        .panel-tab-top { color: #cbd5e1; }
    }

    /* STREAMING_CHUNK:Styling the clickable icons and arrows... */
    .panel-content {
        flex: 1;
        padding: 20px 20px 20px 40px;
        display: flex;
        align-items: center;
        position: relative;
        flex-wrap: wrap;
        gap: 30px;
    }

    .panel-content.grid-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        padding-top: 40px;
        gap: 20px;
        align-content: start;
    }

    .flow-icon {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        text-decoration: none;
        color: #333;
        font-size: 13px;
        width: 100px;
        transition: transform 0.2s, filter 0.2s;
        z-index: 2;
    }

    .flow-icon:hover {
        transform: translateY(-3px);
        filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
    }

    @media (prefers-color-scheme: dark) {
        .flow-icon { color: #e2e8f0; }
    }

    .icon-img {
        font-size: 32px;
        margin-bottom: 8px;
        background: #fff;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.08);
        border: 1px solid #e2e8f0;
    }

    @media (prefers-color-scheme: dark) {
        .icon-img { background: #0f172a; border-color: #334155; }
    }

    .flow-arrow {
        color: #94a3b8;
        font-size: 24px;
        font-weight: bold;
        margin: 0 -10px;
    }

    .workflow-row {
        display: flex;
        align-items: center;
        width: 100%;
    }
</style>

<div class="workflow-container">
    
    <!-- LEFT COLUMN: Main Workflows -->
    <div class="panel-group">
        
        <!-- VENDORS PANEL -->
        <div class="flow-panel">
            <div class="panel-tab">Vendors</div>
            <div class="panel-content">
                <div class="workflow-row">
                    <a href="<?= APP_URL ?>/purchase" class="flow-icon">
                        <div class="icon-img">🛒</div>
                        Purchase Orders
                    </a>
                    <span class="flow-arrow">&rarr;</span>
                    <a href="<?= APP_URL ?>/expenses" class="flow-icon">
                        <div class="icon-img">🧾</div>
                        Enter Bills / Expenses
                    </a>
                </div>
            </div>
        </div>

        <!-- CUSTOMERS PANEL -->
        <div class="flow-panel" style="min-height: 250px;">
            <div class="panel-tab">Customers</div>
            <div class="panel-content" style="flex-direction: column; align-items: flex-start; gap: 40px;">
                
                <!-- Top Row: Sales Flow -->
                <div class="workflow-row">
                    <a href="<?= APP_URL ?>/crm" class="flow-icon">
                        <div class="icon-img">👥</div>
                        Leads & CRM
                    </a>
                    <span class="flow-arrow">&rarr;</span>
                    <a href="<?= APP_URL ?>/estimate" class="flow-icon">
                        <div class="icon-img">📝</div>
                        Quotes & Estimates
                    </a>
                    <span class="flow-arrow">&rarr;</span>
                    <a href="<?= APP_URL ?>/sales" class="flow-icon">
                        <div class="icon-img">📄</div>
                        Create Invoices
                    </a>
                    <span class="flow-arrow">&rarr;</span>
                    <a href="<?= APP_URL ?>/banking" class="flow-icon">
                        <div class="icon-img">💰</div>
                        Receive Payments
                    </a>
                </div>

                <!-- Bottom Row: Other Customer Actions -->
                <div class="workflow-row" style="gap: 40px;">
                    <a href="<?= APP_URL ?>/territory" class="flow-icon">
                        <div class="icon-img">🗺️</div>
                        Territory Routing
                    </a>
                    <a href="<?= APP_URL ?>/creditnote" class="flow-icon">
                        <div class="icon-img">↩️</div>
                        Refunds & Credits
                    </a>
                </div>
            </div>
        </div>

        <!-- EMPLOYEES PANEL -->
        <div class="flow-panel">
            <div class="panel-tab">Employees</div>
            <div class="panel-content">
                <div class="workflow-row">
                    <a href="<?= APP_URL ?>/hrm" class="flow-icon">
                        <div class="icon-img">🧑‍💼</div>
                        Employee Directory
                    </a>
                    <span class="flow-arrow">&rarr;</span>
                    <a href="<?= APP_URL ?>/hrm/payroll" class="flow-icon">
                        <div class="icon-img">⏱️</div>
                        Run Payroll
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- RIGHT COLUMN: Company & Banking -->
    <div class="panel-group">
        
        <!-- COMPANY PANEL -->
        <div class="flow-panel">
            <div class="panel-tab-top">Company</div>
            <div class="panel-content grid-content">
                <a href="<?= APP_URL ?>/accounting/coa" class="flow-icon">
                    <div class="icon-img">📚</div>
                    Chart of Accounts
                </a>
                <a href="<?= APP_URL ?>/inventory" class="flow-icon">
                    <div class="icon-img">📦</div>
                    Items & Services
                </a>
                <a href="<?= APP_URL ?>/settings" class="flow-icon">
                    <div class="icon-img">⚙️</div>
                    Company Settings
                </a>
                <a href="<?= APP_URL ?>/report" class="flow-icon">
                    <div class="icon-img">📊</div>
                    Financial Reports
                </a>
            </div>
        </div>

        <!-- BANKING PANEL -->
        <div class="flow-panel" style="flex: 1;">
            <div class="panel-tab-top">Banking</div>
            <div class="panel-content grid-content">
                <a href="<?= APP_URL ?>/banking" class="flow-icon">
                    <div class="icon-img">🏦</div>
                    Record Deposits
                </a>
                <a href="<?= APP_URL ?>/accounting/journal" class="flow-icon">
                    <div class="icon-img">📓</div>
                    Journal Entries
                </a>
                <a href="<?= APP_URL ?>/banking" class="flow-icon">
                    <div class="icon-img">✅</div>
                    Reconcile
                </a>
                <a href="<?= APP_URL ?>/cheque" class="flow-icon">
                    <div class="icon-img">✍️</div>
                    Cheque Management
                </a>
            </div>
        </div>

    </div>
</div>