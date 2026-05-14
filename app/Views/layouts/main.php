<?php
$db = new Database();
$unreadCount = 0;
if (isset($_SESSION['user_id'])) {
    $db->query("SELECT COUNT(*) as unread FROM notifications WHERE user_id = :uid AND is_read = 0");
    $db->bind(':uid', $_SESSION['user_id']);
    $countRow = $db->single();
    if ($countRow) {
        $unreadCount = $countRow->unread;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - <?= $data['title'] ?? 'Dashboard' ?></title>
    <style>
        :root {
            --mac-bg: rgba(255, 255, 255, 0.6);
            --mac-border: rgba(0, 0, 0, 0.1);
            --bg-color: #f4f5f7;
            --sidebar-bg: #1e1e2d;
            --text-main: #333;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --mac-bg: rgba(30, 30, 30, 0.6);
                --mac-border: rgba(255, 255, 255, 0.1);
                --bg-color: #121212;
                --text-main: #e0e0e0;
            }
        }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: var(--bg-color); color: var(--text-main); display: flex; flex-direction: column; height: 100vh; overflow: hidden; }
        
        .mac-menubar { height: 28px; background: var(--mac-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-bottom: 1px solid var(--mac-border); display: flex; align-items: center; padding: 0 15px; font-size: 13px; font-weight: 500; z-index: 1000; }
        .mac-menubar-left, .mac-menubar-right { display: flex; align-items: center; gap: 15px; }
        .mac-menubar-right { margin-left: auto; }
        .mac-menu-item { cursor: pointer; padding: 0 5px; text-decoration: none; color: var(--text-main); display: flex; align-items: center; gap: 5px;}
        .mac-menu-item.brand { font-weight: 700; }
        
        .notif-badge { background: #ff3b30; color: white; border-radius: 10px; padding: 1px 6px; font-size: 10px; font-weight: bold; min-width: 14px; text-align: center; }
        
        .app-container { display: flex; flex: 1; overflow: hidden; }
        .sidebar { width: 250px; background-color: var(--sidebar-bg); color: #fff; display: flex; flex-direction: column; padding-top: 20px; overflow-y: auto; }
        .sidebar a { color: #a2a3b7; text-decoration: none; padding: 10px 20px; display: block; font-size: 14px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background: rgba(255,255,255,0.05); border-left: 3px solid #0066cc; }
        .sidebar-header { padding: 15px 20px 5px 20px; font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; }
        .main-content { flex: 1; padding: 30px; overflow-y: auto; }
        
        .card { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        @media (prefers-color-scheme: dark) { .card { background: #1e1e2d; } }
        .user-badge { display: inline-block; background: #0066cc; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 11px; margin-left: 10px; text-transform: uppercase; }
    </style>
</head>
<body>

    <div class="mac-menubar">
        <div class="mac-menubar-left">
            <span class="mac-menu-item brand"> CURTISS</span>
            <span class="mac-menu-item">File</span>
            <span class="mac-menu-item">Edit</span>
            <span class="mac-menu-item">View</span>
            <span class="mac-menu-item">Accounting</span>
        </div>
        <div class="mac-menubar-right">
            <!-- Notification Bell Icon -->
            <a href="<?= APP_URL ?>/notification" class="mac-menu-item" title="Notifications">
                🔔 <?php if($unreadCount > 0): ?><span class="notif-badge"><?= $unreadCount ?></span><?php endif; ?>
            </a>
            <span class="mac-menu-item">👤 <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
            <span class="mac-menu-item" id="clock">Loading...</span>
        </div>
    </div>

    <div class="app-container">
        <nav class="sidebar">
            <a href="<?= APP_URL ?>/dashboard">Dashboard</a>
            <a href="<?= APP_URL ?>/crm">CRM & Leads</a>
            <a href="<?= APP_URL ?>/territory" style="color: #0066cc;">Territory & Routing</a>
            <a href="<?= APP_URL ?>/estimate">Estimates & Quotes</a>
            <a href="<?= APP_URL ?>/sales">Sales & AR</a>
            <a href="<?= APP_URL ?>/creditnote">Credit Notes</a>
            
            <div class="sidebar-header">Operations</div>
            <a href="<?= APP_URL ?>/purchase">Procurement (POs)</a>
            <a href="<?= APP_URL ?>/expenses">Expenses & AP</a>
            <a href="<?= APP_URL ?>/inventory">Products & Services</a>
            <a href="<?= APP_URL ?>/hrm">HRM & Payroll</a>
            <a href="<?= APP_URL ?>/asset">Fixed Assets</a>
            
            <div class="sidebar-header">Accounting</div>
            <a href="<?= APP_URL ?>/cheque" style="color: #0066cc;">Cheque Management</a>
            <a href="<?= APP_URL ?>/banking">Banking & Reconcile</a>
            <a href="<?= APP_URL ?>/accounting/coa">Chart of Accounts</a>
            <a href="<?= APP_URL ?>/accounting/journal">Journal Entries</a>
            <a href="<?= APP_URL ?>/budget">Budgeting & Variance</a>
            <a href="<?= APP_URL ?>/report">Financial Reports</a>
            
            <?php if(isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Accountant')): ?>
                <div class="sidebar-header">System Admin</div>
                <a href="<?= APP_URL ?>/tax">Tax Rates</a>
                <a href="<?= APP_URL ?>/accounting/close_year" style="color: #ff9800;">Year-End Close</a>
            <?php endif; ?>

            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                <a href="<?= APP_URL ?>/settings">Company Settings</a>
                <a href="<?= APP_URL ?>/user">User Management</a>
                <a href="<?= APP_URL ?>/audit" style="color: #c62828;">Audit Trail</a>
            <?php endif; ?>

            <a href="<?= APP_URL ?>/auth/logout" style="margin-top:20px; color:#ff3b30; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">Logout</a>
        </nav>

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

    <script>
        function updateClock() {
            const now = new Date();
            const options = { weekday: 'short', hour: 'numeric', minute: '2-digit' };
            document.getElementById('clock').innerText = now.toLocaleDateString('en-US', options).replace(',', '');
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>