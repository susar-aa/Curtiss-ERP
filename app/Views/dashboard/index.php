<?php
/* Glassmorphism Dashboard Redesign */
$currentUrl = $_GET['url'] ?? 'dashboard';
?>
<style>
    /* --- GLASSMORPHISM DASHBOARD LAYOUT --- */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 7fr 3fr;
        gap: 20px;
        min-height: 70vh;
    }

    .panel-group {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* Flow Panel - Glass Card Style */
    .flow-panel {
        position: relative;
        background: var(--card-bg, rgba(255,255,255,0.78));
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid var(--card-border, rgba(255,255,255,0.4));
        border-radius: 16px;
        box-shadow: var(--card-shadow, 0 4px 20px rgba(0,0,0,0.06));
        min-height: 120px;
        overflow: hidden;
        transition: box-shadow 0.3s ease;
    }
    
    .flow-panel:hover {
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    /* Top accent line */
    .flow-panel::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--text-accent, #4f46e5), var(--text-accent-light, #818cf8));
        border-radius: 16px 16px 0 0;
        opacity: 0.6;
    }

    @media (prefers-color-scheme: dark) {
        .flow-panel {
            background: var(--card-bg, rgba(20,20,38,0.82));
            border-color: var(--card-border, rgba(255,255,255,0.08));
        }
    }

    /* Side label tab */
    .panel-tab {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        background: linear-gradient(180deg, var(--text-accent, #4f46e5), var(--text-accent-light, #818cf8));
        color: #fff;
        writing-mode: vertical-rl;
        text-orientation: mixed;
        transform: rotate(180deg);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px 8px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        border-radius: 16px 0 0 16px;
        width: 28px;
        opacity: 0.85;
    }

    .panel-tab-top {
        padding: 10px 16px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-muted, #6b7280);
        border-bottom: 1px solid var(--mega-divider, rgba(0,0,0,0.06));
    }

    .panel-content {
        flex: 1;
        padding: 24px 20px 20px 48px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 24px;
        min-height: 100px;
    }

    .panel-content.grid-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        padding: 16px;
        align-content: start;
    }

    /* Workflow row (flow arrows) */
    .workflow-row {
        display: flex;
        align-items: center;
        width: 100%;
        flex-wrap: wrap;
        gap: 8px;
    }

    /* Flow Icon - Glass Card Style */
    .flow-icon {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        text-decoration: none;
        color: var(--text-main, #1a1a2e);
        font-size: 12px;
        font-weight: 500;
        width: 90px;
        transition: transform 0.2s ease, filter 0.2s ease;
        z-index: 2;
        gap: 6px;
    }

    .flow-icon:hover {
        transform: translateY(-4px);
    }

    .icon-img {
        font-size: 24px;
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--mega-icon-bg, rgba(255,255,255,0.7));
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid var(--mega-icon-border, rgba(229,231,235,0.6));
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        transition: background 0.2s, border-color 0.2s, box-shadow 0.2s;
        color: var(--text-main, #1a1a2e);
    }

    .flow-icon:hover .icon-img {
        background: rgba(79, 70, 229, 0.1);
        border-color: var(--text-accent, #4f46e5);
        box-shadow: 0 4px 16px rgba(79, 70, 229, 0.15);
    }

    @media (prefers-color-scheme: dark) {
        .icon-img {
            background: rgba(35, 35, 55, 0.7);
            border-color: rgba(55, 55, 80, 0.6);
        }
    }

    /* Flow Arrow */
    .flow-arrow {
        color: var(--text-muted, #6b7280);
        font-size: 18px;
        font-weight: 300;
        opacity: 0.5;
        flex-shrink: 0;
    }

    /* Dashboard Welcome Header */
    .dashboard-welcome {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        gap: 16px;
    }
    
    .dashboard-welcome h2 {
        font-size: 22px;
        font-weight: 700;
        color: var(--text-main, #1a1a2e);
        margin: 0;
    }
    
    .dashboard-welcome h2 span {
        color: var(--text-accent, #4f46e5);
    }
    
    .dashboard-welcome .greeting {
        font-size: 14px;
        color: var(--text-muted, #6b7280);
        font-weight: 400;
    }

    /* Quick Stats Row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .stat-card {
        background: var(--card-bg, rgba(255,255,255,0.78));
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid var(--card-border, rgba(255,255,255,0.4));
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: var(--card-shadow, 0 4px 20px rgba(0,0,0,0.06));
        transition: box-shadow 0.3s ease, transform 0.2s ease;
        cursor: default;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    
    .stat-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .stat-card .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
        background: rgba(79, 70, 229, 0.08);
        color: var(--text-accent, #4f46e5);
    }
    
    .stat-card .stat-icon.green {
        background: rgba(16, 185, 129, 0.08);
        color: #10b981;
    }
    
    .stat-card .stat-icon.orange {
        background: rgba(245, 158, 11, 0.08);
        color: #f59e0b;
    }
    
    .stat-card .stat-icon.blue {
        background: rgba(59, 130, 246, 0.08);
        color: #3b82f6;
    }
    
    .stat-card .stat-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .stat-card .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-main, #1a1a2e);
        line-height: 1.2;
    }
    
    .stat-card .stat-label {
        font-size: 12px;
        color: var(--text-muted, #6b7280);
        font-weight: 500;
    }

    @media (prefers-color-scheme: dark) {
        .stat-card {
            background: var(--card-bg, rgba(20,20,38,0.82));
            border-color: var(--card-border, rgba(255,255,255,0.08));
        }
    }

    /* Responsive adjustments */
    @media (max-width: 900px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 500px) {
        .stats-row {
            grid-template-columns: 1fr;
        }
        .panel-content {
            padding: 16px 12px 16px 44px;
        }
        .flow-icon {
            width: 70px;
        }
        .icon-img {
            width: 44px;
            height: 44px;
            font-size: 20px;
        }
    }
</style>

<!-- Dashboard Top Bar -->
<div class="dashboard-topbar">

    <!-- Search -->
    <div class="dashboard-search" onclick="this.querySelector('input').focus()">
        <i class="ph ph-magnifying-glass"></i>
        <input type="text" id="dashSearch" placeholder="Search customers, invoices, products..." autocomplete="off">
    </div>

    <!-- Action Buttons -->
    <div class="dashboard-actions">

        <?php if (!empty($storeUrl)): ?>
        <a href="<?= htmlspecialchars($storeUrl) ?>" target="_blank" class="dash-icon-btn" title="Open Store">
            <i class="ph ph-storefront"></i>
        </a>
        <?php endif; ?>

        <a href="<?= APP_URL ?>/notification" class="dash-icon-btn" title="Notifications">
            <i class="ph ph-bell"></i>
            <?php if ($notifCount > 0): ?>
                <span class="dash-notif-badge"><?= $notifCount ?></span>
            <?php endif; ?>
        </a>

        <!-- User Pill -->
        <div class="dash-user-pill">
            <div class="dash-user-avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 2)) ?></div>
            <div class="dash-user-info">
                <span class="dash-user-name"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
                <span class="dash-user-role"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Staff')) ?></span>
            </div>
        </div>

        <a href="<?= APP_URL ?>/auth/logout" class="dash-icon-btn danger" title="Sign out">
            <i class="ph ph-sign-out"></i>
        </a>

    </div>
</div>

<!-- Dashboard Welcome -->
<div class="dashboard-welcome">
    <div>
        <h2>Good <?= date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening') ?>, <span><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span></h2>
        <div class="greeting">Here's your business overview for today</div>
    </div>
</div>

<!-- Quick Stats -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon"><i class="ph ph-currency-dollar"></i></div>
        <div class="stat-info">
            <div class="stat-value">$0.00</div>
            <div class="stat-label">Today's Sales</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="ph ph-shopping-cart"></i></div>
        <div class="stat-info">
            <div class="stat-value">0</div>
            <div class="stat-label">Pending Orders</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="ph ph-clock"></i></div>
        <div class="stat-info">
            <div class="stat-value">0</div>
            <div class="stat-label">Overdue Invoices</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="ph ph-package"></i></div>
        <div class="stat-info">
            <div class="stat-value">0</div>
            <div class="stat-label">Low Stock Items</div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    
    <!-- LEFT COLUMN: Main Workflows -->
    <div class="panel-group">
        
        <!-- VENDORS PANEL -->
        <div class="flow-panel">
            <div class="panel-tab">Vendors</div>
            <div class="panel-content">
                <div class="workflow-row">
                    <a href="<?= APP_URL ?>/purchase" class="flow-icon">
                        <div class="icon-img"><i class="ph ph-shopping-cart"></i></div>
                        Purchase Orders
                    </a>
                    <span class="flow-arrow">&rarr;</span>
                    <a href="<?= APP_URL ?>/expenses" class="flow-icon">
                        <div class="icon-img"><i class="ph ph-receipt"></i></div>
                        Enter Bills
                    </a>
                </div>
            </div>
        </div>

        <!-- CUSTOMERS PANEL -->
        <div class="flow-panel" style="min-height: 220px;">
            <div class="panel-tab">Customers</div>
            <div class="panel-content" style="flex-direction: column; align-items: flex-start; gap: 28px;">
                
                <!-- Top Row: Sales Flow -->
                <div class="workflow-row">
                    <a href="<?= APP_URL ?>/crm" class="flow-icon">
                        <div class="icon-img"><i class="ph ph-briefcase"></i></div>
                        Leads & CRM
                    </a>
                    <span class="flow-arrow">&rarr;</span>
                    <a href="<?= APP_URL ?>/estimate" class="flow-icon">
                        <div class="icon-img"><i class="ph ph-file-text"></i></div>
                        Estimates
                    </a>
                    <span class="flow-arrow">&rarr;</span>
                    <a href="<?= APP_URL ?>/sales" class="flow-icon">
                        <div class="icon-img"><i class="ph ph-credit-card"></i></div>
                        Invoices
                    </a>
                    <span class="flow-arrow">&rarr;</span>
                    <a href="<?= APP_URL ?>/banking" class="flow-icon">
                        <div class="icon-img"><i class="ph ph-hand-coins"></i></div>
                        Payments
                    </a>
                </div>

                <!-- Bottom Row: Other Customer Actions -->
                <div class="workflow-row" style="gap: 32px;">
                    <a href="<?= APP_URL ?>/territory" class="flow-icon">
                        <div class="icon-img"><i class="ph ph-map-trifold"></i></div>
                        Territory
                    </a>
                    <a href="<?= APP_URL ?>/creditnote" class="flow-icon">
                        <div class="icon-img"><i class="ph ph-arrow-counter-clockwise"></i></div>
                        Refunds
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
                        <div class="icon-img"><i class="ph ph-user-circle-gear"></i></div>
                        Directory
                    </a>
                    <span class="flow-arrow">&rarr;</span>
                    <a href="<?= APP_URL ?>/hrm/payroll" class="flow-icon">
                        <div class="icon-img"><i class="ph ph-bank"></i></div>
                        Payroll
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
                    <div class="icon-img"><i class="ph ph-notebook"></i></div>
                    Chart of Accts
                </a>
                <a href="<?= APP_URL ?>/inventory" class="flow-icon">
                    <div class="icon-img"><i class="ph ph-package"></i></div>
                    Inventory
                </a>
                <a href="<?= APP_URL ?>/settings" class="flow-icon">
                    <div class="icon-img"><i class="ph ph-gear"></i></div>
                    Settings
                </a>
                <a href="<?= APP_URL ?>/report" class="flow-icon">
                    <div class="icon-img"><i class="ph ph-chart-line-up"></i></div>
                    Reports
                </a>
            </div>
        </div>

        <!-- BANKING PANEL -->
        <div class="flow-panel" style="flex: 1;">
            <div class="panel-tab-top">Banking</div>
            <div class="panel-content grid-content">
                <a href="<?= APP_URL ?>/banking" class="flow-icon">
                    <div class="icon-img"><i class="ph ph-bank"></i></div>
                    Deposits
                </a>
                <a href="<?= APP_URL ?>/accounting/journal" class="flow-icon">
                    <div class="icon-img"><i class="ph ph-pen-nib"></i></div>
                    Journal
                </a>
                <a href="<?= APP_URL ?>/banking" class="flow-icon">
                    <div class="icon-img"><i class="ph ph-check-circle"></i></div>
                    Reconcile
                </a>
                <a href="<?= APP_URL ?>/cheque" class="flow-icon">
                    <div class="icon-img"><i class="ph ph-signature"></i></div>
                    Cheques
                </a>
            </div>
        </div>

    </div>
</div>