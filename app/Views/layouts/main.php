<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - <?= $data['title'] ?? 'Dashboard' ?></title>
    <style>
        :root {
            --mac-bg: rgba(255, 255, 255, 0.85);
            --mac-border: rgba(0, 0, 0, 0.15);
            --bg-color: #f4f5f7;
            --text-main: #333;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --mac-bg: rgba(30, 30, 30, 0.85);
                --mac-border: rgba(255, 255, 255, 0.15);
                --bg-color: #121212;
                --text-main: #e0e0e0;
            }
        }
        
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        .mac-menubar {
            height: 30px;
            background: var(--mac-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--mac-border);
            display: flex;
            align-items: center;
            padding: 0 15px;
            font-size: 13px;
            font-weight: 500;
            z-index: 2000;
        }
        
        .mac-menubar-left, .mac-menubar-right {
            display: flex;
            align-items: center;
            height: 100%;
        }
        
        .mac-menubar-right { margin-left: auto; gap: 15px; }

        .mac-menu-container {
            height: 100%;
            display: flex;
            position: relative;
        }

        .mac-menu-item {
            cursor: pointer;
            padding: 0 12px;
            display: flex;
            align-items: center;
            height: 100%;
            color: var(--text-main);
            text-decoration: none;
        }

        .mac-menu-item.brand { font-weight: 700; font-size: 14px;}

        /* Hover effect mimics macOS top bar selection */
        .mac-menu-container:hover .mac-menu-item {
            background-color: #0066cc;
            color: #fff;
        }

        /* The actual dropdown container */
        .mac-dropdown {
            display: none;
            position: absolute;
            top: 30px; /* Matches menubar height */
            left: 0;
            background: var(--mac-bg);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid var(--mac-border);
            border-top: none;
            min-width: 220px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            border-radius: 0 0 6px 6px;
            padding: 5px 0;
            z-index: 2000;
        }

        .mac-menu-container:hover .mac-dropdown {
            display: block;
        }

        .mac-dropdown a {
            color: var(--text-main);
            padding: 6px 20px;
            text-decoration: none;
            display: block;
            font-size: 13px;
        }

        .mac-dropdown a:hover {
            background-color: #0066cc;
            color: #fff;
        }

        .mac-dropdown hr {
            border: none;
            border-top: 1px solid var(--mac-border);
            margin: 5px 0;
        }
        
        .app-container { display: flex; flex: 1; overflow: hidden; position: relative;}
        .main-content { flex: 1; padding: 30px; padding-bottom: 60px; overflow-y: auto; }
        
        .card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        @media (prefers-color-scheme: dark) { .card { background: #1e1e2d; } }
        
        .badge-alert {
            background: #ff3b30;
            color: #fff;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
            position: absolute;
            top: 4px;
            right: -10px;
        }

        .mac-recent-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: var(--mac-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid var(--mac-border);
            height: 40px;
            z-index: 2000;
            display: flex;
            align-items: center;
            padding: 0 15px;
            box-sizing: border-box;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateY(0);
        }

        .mac-recent-bar.collapsed {
            transform: translateY(100%);
        }

        .recent-toggle-tab {
            position: absolute;
            top: -26px; 
            right: 30px;
            background: var(--mac-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--mac-border);
            border-bottom: none;
            border-radius: 8px 8px 0 0;
            padding: 4px 15px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            color: var(--text-main);
            box-shadow: 0 -2px 5px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .recent-links-container {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            scrollbar-width: none; 
            flex: 1;
            align-items: center;
        }

        .recent-links-container::-webkit-scrollbar { display: none; }

        .recent-link {
            font-size: 12px;
            color: var(--text-main);
            text-decoration: none;
            background: rgba(0,0,0,0.05);
            padding: 4px 12px;
            border-radius: 12px;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: 0.2s;
        }
        @media (prefers-color-scheme: dark) { .recent-link { background: rgba(255,255,255,0.1); } }

        .recent-link:hover {
            background: #0066cc;
            color: #fff;
        }
    </style>
</head>
<body>

    <?php 
        $db = new Database();
        $notifCount = 0;
        
        // Safely fetch notifications if the user is logged in and the table exists
        if (isset($_SESSION['user_id'])) {
            try {
                $db->query("SELECT COUNT(*) as unread FROM notifications WHERE user_id = :uid AND is_read = 0");
                $db->bind(':uid', $_SESSION['user_id']);
                $row = $db->single();
                if ($row) {
                    $notifCount = $row->unread;
                }
            } catch (Exception $e) {
                // Table might not exist yet; ignore safely
                $notifCount = 0;
            }
        }
    ?>

    <div class="mac-menubar">
        <div class="mac-menubar-left">
            <a href="<?= APP_URL ?>/dashboard" class="mac-menu-item brand"> <?= APP_NAME ?></a>
            
            <div class="mac-menu-container">
                <div class="mac-menu-item">Sales & CRM</div>
                <div class="mac-dropdown">
                    <a href="<?= APP_URL ?>/crm">Leads & CRM</a>
                    <a href="<?= APP_URL ?>/customer">Customer Center</a>
                    <a href="<?= APP_URL ?>/dunning">Dunning & AR Reminders</a>
                    <a href="<?= APP_URL ?>/estimate">Quotes & Estimates</a>
                    <a href="<?= APP_URL ?>/sales">Invoices & AR</a>
                    <a href="<?= APP_URL ?>/creditnote">Credit Notes</a>
                    <a href="<?= APP_URL ?>/territory">Territory & Routing</a>
                </div>
            </div>

            <div class="mac-menu-container">
                <div class="mac-menu-item">Supply Chain</div>
                <div class="mac-dropdown">
                    <a href="<?= APP_URL ?>/vendor">Vendor Center</a>
                    <a href="<?= APP_URL ?>/inventory">Products & Inventory</a>
                    <a href="<?= APP_URL ?>/category">Product Categories</a>
                    <a href="<?= APP_URL ?>/variation">Variations & Attributes</a>
                    <a href="<?= APP_URL ?>/warehouse">Warehouse Management</a>
                    <hr>
                    <a href="<?= APP_URL ?>/purchase">Purchase Orders</a>
                    <a href="<?= APP_URL ?>/grn">Goods Receipts (GRN)</a>
                    <a href="<?= APP_URL ?>/expenses">Expenses & AP</a>
                </div>
            </div>

            <div class="mac-menu-container">
                <div class="mac-menu-item">Operations</div>
                <div class="mac-dropdown">
                    <a href="<?= APP_URL ?>/cheque">Cheque Management</a>
                    <hr>
                    <a href="<?= APP_URL ?>/hrm">HRM & Employees</a>
                    <a href="<?= APP_URL ?>/hrm/payroll">Run Payroll</a>
                    <a href="<?= APP_URL ?>/project">Projects & Tasks</a>
                </div>
            </div>

            <div class="mac-menu-container">
                <div class="mac-menu-item">Accounting</div>
                <div class="mac-dropdown">
                    <a href="<?= APP_URL ?>/accounting/coa">Chart of Accounts</a>
                    <a href="<?= APP_URL ?>/accounting/journal">Journal Entries</a>
                    <hr>
                    <a href="<?= APP_URL ?>/banking">Banking & Registers</a>
                    <a href="<?= APP_URL ?>/asset">Fixed Assets Register</a>
                </div>
            </div>

            <div class="mac-menu-container">
                <div class="mac-menu-item">Analytics</div>
                <div class="mac-dropdown">
                    <a href="<?= APP_URL ?>/budget">Budgets vs Actuals</a>
                    <a href="<?= APP_URL ?>/report">Financial Reports Hub</a>
                </div>
            </div>

            <?php if(isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Manager' || $_SESSION['role'] === 'Accountant')): ?>
            <div class="mac-menu-container">
                <div class="mac-menu-item">Admin</div>
                <div class="mac-dropdown">
                    <a href="<?= APP_URL ?>/settings">Company Settings</a>
                    <?php if($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Accountant'): ?>
                        <a href="<?= APP_URL ?>/tax">Tax Rates & Rules</a>
                        <hr>
                        <a href="<?= APP_URL ?>/accounting/close_year" style="color: #ff3b30;">Close Financial Year</a>
                    <?php endif; ?>
                    <?php if($_SESSION['role'] === 'Admin'): ?>
                        <hr>
                        <a href="<?= APP_URL ?>/user">User Management</a>
                        <a href="<?= APP_URL ?>/audit">System Audit Trail</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="mac-menubar-right">
            <a href="<?= APP_URL ?>/notification" class="mac-menu-item" style="position: relative;">
                🔔
                <?php if($notifCount > 0): ?>
                    <span class="badge-alert"><?= $notifCount ?></span>
                <?php endif; ?>
            </a>
            <span class="mac-menu-item" style="cursor:default;">👤 <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
            <span class="mac-menu-item" id="clock" style="cursor:default;"></span>
            
            <a href="<?= APP_URL ?>/auth/logout" class="mac-menu-item" style="color:#ff3b30; font-weight:bold;">Logout</a>
        </div>
    </div>

    <div class="app-container">
        <main class="main-content">
            <?php 
            if (isset($data['content_view'])) {
                require_once '../app/Views/' . $data['content_view'] . '.php';
            } else {
                echo "<p>View not found.</p>";
            }
            ?>
        </main>
    </div>

    <div id="recentBar" class="mac-recent-bar">
        <div class="recent-toggle-tab" onclick="toggleRecentBar()">
            <span id="recentToggleIcon">▼</span> Recent Pages
        </div>
        <div style="font-size: 11px; color:#888; font-weight:bold; margin-right: 15px; text-transform:uppercase;">History:</div>
        <div class="recent-links-container" id="recentLinksContainer">
            <!-- JavaScript will populate this dynamically -->
        </div>
    </div>

    <script>
        // Clock Logic
        function updateClock() {
            const now = new Date();
            const options = { weekday: 'short', hour: 'numeric', minute: '2-digit' };
            document.getElementById('clock').innerText = now.toLocaleDateString('en-US', options).replace(',', '');
        }
        setInterval(updateClock, 1000);
        updateClock();

        // --- NEW: Recent History Tracker Logic ---
        document.addEventListener("DOMContentLoaded", function() {
            const currentUrl = window.location.href;
            const currentTitle = "<?= addslashes($data['title'] ?? 'Dashboard') ?>";
            
            // Exclude the login/logout pages from history
            if(currentUrl.includes('/auth/')) return;

            // Fetch existing history from Session Storage
            let history = JSON.parse(sessionStorage.getItem('curtiss_history')) || [];
            
            // Remove current URL if it already exists in the array (so we can move it to the front)
            history = history.filter(item => item.url !== currentUrl);
            
            // Add the current page to the absolute front of the array
            history.unshift({ url: currentUrl, title: currentTitle });
            
            // Keep a maximum of 8 recent pages to prevent overflow
            if(history.length > 8) history.pop();
            
            // Save it back to session storage
            sessionStorage.setItem('curtiss_history', JSON.stringify(history));

            // Render the pills to the HTML bar (skipping index 0 since that is the page we are already on)
            const container = document.getElementById('recentLinksContainer');
            if (history.length <= 1) {
                container.innerHTML = '<span style="font-size:12px; color:#888;">No recent pages yet.</span>';
            } else {
                for(let i = 1; i < history.length; i++) {
                    let a = document.createElement('a');
                    a.href = history[i].url;
                    a.className = 'recent-link';
                    a.innerHTML = `📄 ${history[i].title}`;
                    container.appendChild(a);
                }
            }

            // Restore the Collapse state from local storage
            const bar = document.getElementById('recentBar');
            const icon = document.getElementById('recentToggleIcon');
            let isCollapsed = localStorage.getItem('curtiss_recent_collapsed') === 'true';
            
            if(isCollapsed) {
                bar.classList.add('collapsed');
                icon.innerText = '▲';
            }
        });

        function toggleRecentBar() {
            const bar = document.getElementById('recentBar');
            const icon = document.getElementById('recentToggleIcon');
            
            bar.classList.toggle('collapsed');
            const collapsedNow = bar.classList.contains('collapsed');
            
            // Save preference to local storage so it persists across page loads
            localStorage.setItem('curtiss_recent_collapsed', collapsedNow);
            icon.innerText = collapsedNow ? '▲' : '▼';
        }
    </script>
</body>
</html>