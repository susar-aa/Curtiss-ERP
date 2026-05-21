<?php
$hasDelivery = isset($data['active_delivery']) && $data['active_delivery'];
$isInTransit = $hasDelivery && $data['active_delivery']->status === 'In Transit';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $data['title'] ?> - Driver App</title>
    <style>
        :root {
            --app-bg: #f4f5f7;
            --primary: #0066cc;
            --primary-light: #e6f0fa;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f1c40f;
            --surface: #ffffff;
            --text-dark: #111111;
            --text-muted: #666666;
            --border: #e0e0e0;
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.4);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --app-bg: #121212;
                --primary: #3399ff;
                --primary-light: #1a334d;
                --surface: #1e1e2d;
                --text-dark: #ffffff;
                --text-muted: #aaaaaa;
                --border: #333333;
                --glass-bg: rgba(30, 30, 45, 0.7);
                --glass-border: rgba(255, 255, 255, 0.05);
            }
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #000;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            justify-content: center;
            color: var(--text-dark);
        }

        .mobile-container {
            width: 100%;
            max-width: 480px;
            height: 100vh;
            background-color: var(--app-bg);
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }

        .app-header {
            background-color: var(--surface);
            backdrop-filter: blur(10px);
            background: var(--glass-bg);
            border-bottom: 1px solid var(--glass-border);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 10;
        }

        .app-header h1 {
            margin: 0;
            font-size: 18px;
            color: var(--text-dark);
            font-weight: 700;
        }

        .app-content {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            -webkit-overflow-scrolling: touch;
        }

        /* Bottom Navigation Bar */
        .bottom-nav {
            background-color: var(--surface);
            backdrop-filter: blur(10px);
            background: var(--glass-bg);
            border-top: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-around;
            padding: 12px 0;
            padding-bottom: env(safe-area-inset-bottom, 12px);
            z-index: 10;
        }

        .nav-item {
            text-decoration: none;
            color: var(--text-muted);
            font-size: 11px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            font-weight: 600;
            transition: all 0.2s ease-in-out;
        }

        .nav-item.active { color: var(--primary); transform: translateY(-2px); }
        .nav-icon { font-size: 20px; }

        /* Shared UI Elements */
        .card { 
            background: var(--surface); 
            border-radius: 16px; 
            padding: 20px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.03); 
            margin-bottom: 20px; 
            border: 1px solid var(--border);
        }
        
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-info { background: var(--primary-light); color: var(--primary); }
        .badge-success { background: rgba(46, 204, 113, 0.15); color: var(--success); }
        .badge-warning { background: rgba(241, 196, 15, 0.15); color: var(--warning); }
        .badge-danger { background: rgba(231, 76, 60, 0.15); color: var(--danger); }

        .form-label { display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-input, select { width: 100%; padding: 14px; border: 1px solid var(--border); border-radius: 10px; background: var(--app-bg); color: var(--text-dark); font-size: 15px; box-sizing: border-box; margin-bottom: 18px; outline: none; transition: border-color 0.2s; }
        .form-input:focus, select:focus { border-color: var(--primary); }
        
        .btn-primary { width: 100%; background: var(--primary); color: #fff; border: none; padding: 15px; border-radius: 10px; font-size: 15px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 12px rgba(0, 102, 204, 0.2); text-align: center; display: inline-block; box-sizing: border-box; text-decoration: none; transition: opacity 0.2s; }
        .btn-primary:active { opacity: 0.9; }

        .btn-secondary { width: 100%; background: var(--border); color: var(--text-dark); border: none; padding: 15px; border-radius: 10px; font-size: 15px; font-weight: bold; cursor: pointer; text-align: center; display: inline-block; box-sizing: border-box; text-decoration: none; }

        .btn-disabled { background: var(--border) !important; color: var(--text-muted) !important; cursor: not-allowed !important; box-shadow: none !important; }
        
        .alert { padding: 12px 16px; border-radius: 10px; font-size: 14px; margin-bottom: 18px; line-height: 1.4; }
        .alert-success { background: rgba(46, 204, 113, 0.15); color: var(--success); border: 1px solid rgba(46, 204, 113, 0.3); }
        .alert-danger { background: rgba(231, 76, 60, 0.15); color: var(--danger); border: 1px solid rgba(231, 76, 60, 0.3); }
    </style>
</head>
<body>
    <div class="mobile-container">
        
        <header class="app-header">
            <h1><?= htmlspecialchars($data['title']) ?></h1>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <a href="<?= APP_URL ?>/driver/auth/logout" style="color:var(--danger); text-decoration:none; font-size:14px; font-weight:bold;">Logout</a>
                    <span style="color: var(--border);">|</span>
                    <a href="<?= APP_URL ?>/dashboard" style="color:var(--primary); text-decoration:none; font-size:14px; font-weight:bold;">Exit to ERP</a>
                </div>
            <?php endif; ?>
        </header>

        <main class="app-content">
            <?php if (!empty($data['success'])): ?>
                <div class="alert alert-success">✓ <?= htmlspecialchars($data['success']) ?></div>
            <?php endif; ?>

            <?php if (!empty($data['error'])): ?>
                <div class="alert alert-danger">⚠ <?= htmlspecialchars($data['error']) ?></div>
            <?php endif; ?>

            <?php require_once '../driver_app/Views/' . $data['content_view'] . '.php'; ?>
        </main>

        <?php if (isset($_SESSION['user_id'])): ?>
        <nav class="bottom-nav">
            <a href="<?= APP_URL ?>/driver" class="nav-item <?= $data['content_view'] === 'dashboard' ? 'active' : '' ?>">
                <span class="nav-icon">🚚</span>
                <span>Dashboard</span>
            </a>
            
            <a href="<?= APP_URL ?>/driver/vehicle_stock" class="nav-item <?= $data['content_view'] === 'vehicle_stock' ? 'active' : '' ?>">
                <span class="nav-icon">📦</span>
                <span>Vehicle Stock</span>
            </a>
            
            <a href="#" class="nav-item" onclick="alert('Select a shop from the dashboard list to view the delivery checklist!'); return false;">
                <span class="nav-icon">📝</span>
                <span>Checklist</span>
            </a>
        </nav>
        <?php endif; ?>

    </div>
</body>
</html>
