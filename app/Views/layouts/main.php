<?php

// Enable error reporting to prevent blank 500 errors in the future
// But suppress for AJAX requests to prevent HTML in JSON responses
if (!isset($_SERVER['HTTP_ACCEPT']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

if (!function_exists('hasPermission')) {
    function hasPermission($module, $action = 'view') {
        if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin') {
            return true;
        }
        if (!isset($_SESSION['permissions'])) {
            return false;
        }
        $perms = $_SESSION['permissions'];
        if (!isset($perms[$module])) {
            return false;
        }
        if ($action === 'view') {
            return (bool)($perms[$module]['can_view'] ?? false);
        } elseif ($action === 'create_edit') {
            return (bool)($perms[$module]['can_create_edit'] ?? false);
        } elseif ($action === 'delete') {
            return (bool)($perms[$module]['can_delete'] ?? false);
        }
        return false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - <?= $data['title'] ?? 'Dashboard' ?></title>
    
    <!-- PWA Manifest & Mobile App Support -->
    <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
    <meta name="theme-color" content="#0066cc">
    <link rel="apple-touch-icon" href="<?= APP_URL ?>/icon-192.png">
    
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?= APP_URL ?>/service-worker.js')
                    .then((reg) => console.log('PWA Service Worker registered successfully:', reg.scope))
                    .catch((err) => console.log('PWA Service Worker registration failed:', err));
            });
        }
    </script>
    
    <!-- Phosphor Icons for a clean, modern look (replacing emojis) -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        :root {
            /* Glassmorphism Core Variables */
            --glass-bg: rgba(255, 255, 255, 0.72);
            --glass-border: rgba(255, 255, 255, 0.35);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            --glass-blur: 24px;
            
            --bg-color: #f0f2f6;
            --text-main: #1a1a2e;
            --text-muted: #6b7280;
            --text-accent: #4f46e5;
            --text-accent-light: #818cf8;
            
            /* Mega Menu Variables */
            --mega-bg: rgba(255, 255, 255, 0.88);
            --mega-card-bg: rgba(249, 250, 251, 0.8);
            --mega-card-hover: rgba(238, 242, 255, 0.9);
            --mega-icon-border: rgba(229, 231, 235, 0.6);
            --mega-icon-bg: rgba(255, 255, 255, 0.7);
            --mega-divider: rgba(0, 0, 0, 0.06);
            --mega-hover: rgba(79, 70, 229, 0.06);
            
            /* Dashboard Variables */
            --card-bg: rgba(255, 255, 255, 0.78);
            --card-border: rgba(255, 255, 255, 0.4);
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }
        
        @media (prefers-color-scheme: dark) {
            :root {
                --glass-bg: rgba(20, 20, 35, 0.78);
                --glass-border: rgba(255, 255, 255, 0.10);
                --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
                
                --bg-color: #0f0f1a;
                --text-main: #e8e8f0;
                --text-muted: #9ca3af;
                --text-accent: #818cf8;
                --text-accent-light: #a5b4fc;
                
                --mega-bg: rgba(25, 25, 42, 0.92);
                --mega-card-bg: rgba(35, 35, 55, 0.8);
                --mega-card-hover: rgba(55, 55, 80, 0.9);
                --mega-icon-border: rgba(55, 55, 80, 0.6);
                --mega-icon-bg: rgba(35, 35, 55, 0.7);
                --mega-divider: rgba(255, 255, 255, 0.06);
                --mega-hover: rgba(129, 140, 248, 0.08);
                
                --card-bg: rgba(20, 20, 38, 0.82);
                --card-border: rgba(255, 255, 255, 0.08);
                --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(ellipse at 10% 20%, rgba(79, 70, 229, 0.06) 0%, transparent 50%),
                radial-gradient(ellipse at 90% 80%, rgba(99, 102, 241, 0.05) 0%, transparent 50%);
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        /* --- GLASSMORPHISM FLOATING NAV BAR (Redesigned) --- */
        .glass-nav {
            position: fixed;
            top: 12px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2000;
            display: flex;
            align-items: center;
            height: 54px;
            padding: 0 12px;
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 28px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08), 0 1px 4px rgba(0,0,0,0.04);
            width: calc(100% - 40px);
            max-width: 1400px;
            transition: box-shadow 0.3s ease;
            gap: 8px;
        }
        @media (prefers-color-scheme: dark) {
            .glass-nav {
                background: rgba(22, 22, 38, 0.88);
                border-color: rgba(255,255,255,0.10);
                box-shadow: 0 4px 24px rgba(0,0,0,0.35);
            }
        }
        .glass-nav:hover {
            box-shadow: 0 8px 36px rgba(0, 0, 0, 0.13);
        }

        /* Brand section */
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            flex-shrink: 0;
            padding: 0 6px;
        }
        .nav-brand-logo {
            width: 30px;
            height: 30px;
            background: var(--text-main);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: -0.5px;
            flex-shrink: 0;
        }
        @media (prefers-color-scheme: dark) {
            .nav-brand-logo { background: #e8e8f0; color: #1a1a2e; }
        }
        .nav-brand-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.3px;
        }

        /* Vertical divider */
        .glass-nav-divider {
            width: 1px;
            height: 24px;
            background: rgba(0,0,0,0.08);
            flex-shrink: 0;
            margin: 0 4px;
        }
        @media (prefers-color-scheme: dark) {
            .glass-nav-divider { background: rgba(255,255,255,0.08); }
        }

        /* Nav menu items (left area) */
        .glass-nav-left {
            display: flex;
            align-items: center;
            height: 100%;
            gap: 1px;
            flex-shrink: 0;
        }
        .glass-nav-item {
            cursor: pointer;
            padding: 0 11px;
            display: flex;
            align-items: center;
            gap: 5px;
            height: 36px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 12.5px;
            font-weight: 500;
            border-radius: 20px;
            transition: background 0.18s ease, color 0.18s ease;
            white-space: nowrap;
            position: relative;
        }
        .glass-nav-item:hover {
            background: rgba(79, 70, 229, 0.08);
            color: var(--text-accent);
        }

        /* Center search bar */
        .nav-search-wrap {
            flex: 1;
            min-width: 0;
            display: flex;
            justify-content: center;
            padding: 0 8px;
        }
        .nav-search {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            max-width: 480px;
            height: 36px;
            background: rgba(0, 0, 0, 0.05);
            border-radius: 20px;
            padding: 0 14px 0 14px;
            transition: background 0.2s, box-shadow 0.2s;
            cursor: text;
        }
        @media (prefers-color-scheme: dark) {
            .nav-search { background: rgba(255,255,255,0.07); }
        }
        .nav-search:hover, .nav-search:focus-within {
            background: rgba(0,0,0,0.08);
            box-shadow: 0 0 0 2px rgba(79,70,229,0.18);
        }
        @media (prefers-color-scheme: dark) {
            .nav-search:hover, .nav-search:focus-within { background: rgba(255,255,255,0.11); }
        }
        .nav-search i {
            color: var(--text-muted);
            font-size: 15px;
            flex-shrink: 0;
        }
        .nav-search input {
            border: none;
            background: transparent;
            outline: none;
            font-size: 13px;
            color: var(--text-main);
            width: 100%;
            font-family: inherit;
        }
        .nav-search input::placeholder { color: var(--text-muted); }
        .nav-search-shortcut {
            width: 26px;
            height: 18px;
            background: rgba(0,0,0,0.07);
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 0;
            flex-shrink: 0;
            font-family: inherit;
            cursor: pointer;
        }
        @media (prefers-color-scheme: dark) {
            .nav-search-shortcut { background: rgba(255,255,255,0.1); }
        }

        /* Right action cluster */
        .glass-nav-right {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }
        .nav-icon-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 17px;
            text-decoration: none;
            transition: background 0.18s, color 0.18s;
            position: relative;
            cursor: pointer;
        }
        .nav-icon-btn:hover {
            background: rgba(79, 70, 229, 0.08);
            color: var(--text-accent);
        }
        .nav-icon-btn.active-red { color: #ef4444; }
        .nav-icon-btn.active-red:hover { background: rgba(239,68,68,0.08); color: #ef4444; }

        /* User pill */
        .nav-user-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 3px 10px 3px 12px;
            border-radius: 20px;
            cursor: default;
            transition: background 0.18s;
        }
        .nav-user-pill:hover {
            background: rgba(79,70,229,0.06);
        }
        .nav-user-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            white-space: nowrap;
        }
        .nav-user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            flex-shrink: 0;
            letter-spacing: 0;
        }

        /* Badge */
        .badge-alert {
            background: #ef4444;
            color: #fff;
            border-radius: 10px;
            padding: 2px 5px;
            font-size: 9px;
            font-weight: 700;
            position: absolute;
            top: 3px;
            right: 3px;
            border: 2px solid rgba(255,255,255,0.9);
            line-height: 1.2;
            min-width: 16px;
            text-align: center;
        }

        /* --- MEGA MENU (Glassmorphism) --- */
        .glass-menu-container {
            height: 100%;
            display: flex;
            position: relative;
        }
        .glass-menu-container .mega-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: 8px;
            background: var(--mega-bg);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.15);
            padding: 8px 0;
            z-index: 3000;
            flex-direction: row;
            cursor: default;
            min-width: 200px;
            animation: megaFadeIn 0.2s ease;
        }
        @keyframes megaFadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .glass-menu-container:hover .mega-menu { display: flex; }
        .align-right .mega-menu { left: auto; right: 0; }
        .glass-menu-container:hover .mega-menu .glass-nav-item {
            background: transparent;
            color: var(--text-main);
        }
        .mega-menu-col {
            padding: 12px 20px;
            display: flex;
            flex-direction: column;
            min-width: 220px;
        }
        .mega-menu-col:not(:first-child) { border-left: 1px solid var(--mega-divider); }
        .mega-menu-header {
            font-size: 10px;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.8px;
            margin-bottom: 12px;
            font-weight: 600;
            padding: 0 6px;
        }
        .mega-cards-grid { display: flex; gap: 10px; }
        .mega-card {
            background: var(--mega-card-bg);
            border-radius: 12px;
            padding: 16px;
            width: 130px;
            height: 110px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.2s ease;
            box-sizing: border-box;
            border: 1px solid transparent;
        }
        .mega-card:hover {
            background: var(--mega-card-hover);
            transform: translateY(-2px);
            border-color: var(--glass-border);
        }
        .mega-card .icon { font-size: 22px; color: var(--text-main); }
        .mega-card-text {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .mega-card-text .title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
        }
        .mega-card-text .desc {
            font-size: 11px;
            color: var(--text-muted);
            line-height: 1.3;
        }
        
        /* List Items */
        .mega-list-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 8px 10px;
            margin: 0 -6px;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.2s;
        }
        .mega-list-item:hover {
            background: var(--mega-hover);
        }
        .mega-list-item .icon-wrapper {
            width: 34px;
            height: 34px;
            border: 1px solid var(--mega-icon-border);
            background: var(--mega-icon-bg);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
            color: var(--text-main);
            backdrop-filter: blur(8px);
        }
        .mega-list-item-content {
            display: flex;
            flex-direction: column;
            gap: 2px;
            margin-top: 1px;
        }
        .mega-list-item-content .title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
        }
        .mega-list-item-content .desc {
            font-size: 11px;
            color: var(--text-muted);
        }
        
        /* Overrides for specific colors */
        .text-danger { color: #ef4444 !important; }
        .text-warning { color: #f59e0b !important; }
        .text-primary { color: var(--text-accent) !important; }

        /* Notification Badge */
        .badge-alert {
            background: #ef4444;
            color: #fff;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
            position: absolute;
            top: 2px;
            right: -6px;
            border: 2px solid var(--glass-bg);
            line-height: 1.2;
            min-width: 18px;
            text-align: center;
        }

        /* --- MAIN APP CONTAINER --- */
        .app-container {
            display: flex;
            flex: 1;
            overflow: hidden;
            position: relative;
            margin-top: 82px; /* Space for floating nav bar (12px top + 54px height + 16px gap) */
        }
        
        .main-content {
            flex: 1;
            padding: 28px 32px;
            padding-bottom: 80px;
            overflow-y: auto;
        }
        
        /* Glass Card Component */
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
            transition: box-shadow 0.3s ease, transform 0.2s ease;
        }
        
        .glass-card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        /* --- RECENT BAR (Glassmorphism) --- */
        .mac-recent-bar {
            position: fixed;
            bottom: 16px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--glass-bg);
            backdrop-filter: blur(var(--glass-blur));
            -webkit-backdrop-filter: blur(var(--glass-blur));
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            box-shadow: var(--glass-shadow);
            height: 42px;
            z-index: 2000;
            display: flex;
            align-items: center;
            padding: 0 12px;
            box-sizing: border-box;
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
            min-width: 200px;
            max-width: calc(100% - 64px);
            gap: 10px;
        }
        
        .mac-recent-bar.collapsed {
            transform: translateX(-50%) translateY(calc(100% + 20px));
            opacity: 0;
            pointer-events: none;
        }
        
        .recent-toggle-tab {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            color: var(--text-muted);
            padding: 4px 10px;
            border-radius: 8px;
            transition: background 0.2s, color 0.2s;
            flex-shrink: 0;
            background: rgba(0,0,0,0.03);
        }
        @media (prefers-color-scheme: dark) {
            .recent-toggle-tab { background: rgba(255,255,255,0.05); }
        }
        .recent-toggle-tab:hover {
            background: rgba(79, 70, 229, 0.1);
            color: var(--text-accent);
        }
        
        .recent-links-container {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            scrollbar-width: none;
            flex: 1;
            align-items: center;
            padding: 0 4px;
        }
        .recent-links-container::-webkit-scrollbar { display: none; }
        
        .recent-link {
            font-size: 12px;
            color: var(--text-main);
            text-decoration: none;
            background: rgba(0, 0, 0, 0.04);
            padding: 4px 12px;
            border-radius: 8px;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s, color 0.2s;
            flex-shrink: 0;
        }
        @media (prefers-color-scheme: dark) { .recent-link { background: rgba(255,255,255,0.06); } }
        .recent-link:hover { 
            background: var(--text-accent); 
            color: #fff; 
        }
        
        /* Live Clock in Recent Bar */
        .recent-clock {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
            flex-shrink: 0;
            padding: 0 6px;
            border-left: 1px solid var(--mega-divider);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        /* Utility */
        .hidden { display: none !important; }

        /* Nav page heading (right side of navbar) */
        .nav-page-heading {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 8px 0 12px;
            flex-shrink: 0;
        }
        .nav-page-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--text-accent);
            flex-shrink: 0;
            animation: pulse-dot 2.5s ease-in-out infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.35; }
        }
        .nav-page-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            letter-spacing: -0.2px;
            white-space: nowrap;
            max-width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Dashboard top bar (search + user actions row) */
        .dashboard-topbar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .dashboard-search {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            min-width: 220px;
            max-width: 480px;
            height: 42px;
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 22px;
            padding: 0 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            transition: box-shadow 0.2s, border-color 0.2s;
            cursor: text;
        }
        @media (prefers-color-scheme: dark) {
            .dashboard-search { background: rgba(30,30,50,0.85); border-color: rgba(255,255,255,0.08); }
        }
        .dashboard-search:hover, .dashboard-search:focus-within {
            box-shadow: 0 4px 20px rgba(79,70,229,0.15);
            border-color: rgba(79,70,229,0.3);
        }
        .dashboard-search i { color: var(--text-muted); font-size: 16px; flex-shrink: 0; }
        .dashboard-search input {
            border: none; background: transparent; outline: none;
            font-size: 13.5px; color: var(--text-main); width: 100%; font-family: inherit;
        }
        .dashboard-search input::placeholder { color: var(--text-muted); }
        .dashboard-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-left: auto;
        }
        .dash-icon-btn {
            width: 42px; height: 42px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: var(--text-muted); font-size: 18px; text-decoration: none;
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(0,0,0,0.07);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: background 0.18s, color 0.18s, box-shadow 0.18s;
            position: relative;
            cursor: pointer;
        }
        @media (prefers-color-scheme: dark) {
            .dash-icon-btn { background: rgba(30,30,50,0.85); border-color: rgba(255,255,255,0.08); }
        }
        .dash-icon-btn:hover {
            background: rgba(79,70,229,0.1);
            color: var(--text-accent);
            box-shadow: 0 4px 16px rgba(79,70,229,0.15);
        }
        .dash-icon-btn.danger { color: #ef4444; }
        .dash-icon-btn.danger:hover { background: rgba(239,68,68,0.1); box-shadow: 0 4px 16px rgba(239,68,68,0.15); }
        .dash-user-pill {
            display: flex; align-items: center; gap: 10px;
            padding: 5px 14px 5px 5px;
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(0,0,0,0.07);
            border-radius: 22px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            cursor: default;
            transition: background 0.18s;
        }
        @media (prefers-color-scheme: dark) {
            .dash-user-pill { background: rgba(30,30,50,0.85); border-color: rgba(255,255,255,0.08); }
        }
        .dash-user-pill:hover { background: rgba(79,70,229,0.06); }
        .dash-user-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 12px; font-weight: 700; flex-shrink: 0;
        }
        .dash-user-info { display: flex; flex-direction: column; gap: 1px; }
        .dash-user-name { font-size: 13px; font-weight: 600; color: var(--text-main); white-space: nowrap; }
        .dash-user-role { font-size: 11px; color: var(--text-muted); font-weight: 400; }
        .dash-notif-badge {
            background: #ef4444; color: #fff;
            border-radius: 10px; padding: 2px 5px; font-size: 9px; font-weight: 700;
            position: absolute; top: 3px; right: 3px;
            border: 2px solid rgba(255,255,255,0.9); line-height: 1.2; min-width: 16px; text-align: center;
        }
    </style>
</head>
<body>

    <?php 
        $db = new Database();
        $notifCount = 0;
        $storeUrl = 'http://localhost/Curtiss%20E%20Commerce';
        
        if (isset($_SESSION['user_id'])) {
            try {
                $db->query("SELECT COUNT(*) as unread FROM notifications WHERE user_id = :uid AND is_read = 0");
                $db->bind(':uid', $_SESSION['user_id']);
                $row = $db->single();
                if ($row) {
                    $notifCount = $row->unread;
                }
            } catch (Exception $e) {
                $notifCount = 0;
            }

            try {
                $db->query("SELECT ecommerce_store_url FROM company_settings LIMIT 1");
                $sett = $db->single();
                if ($sett && !empty($sett->ecommerce_store_url)) {
                    $storeUrl = $sett->ecommerce_store_url;
                }
            } catch (Exception $e) {
                // Keep default
            }
        }
        
        // Determine page title for the indicator
        $currentPageTitle = $data['title'] ?? 'Dashboard';
        // Try to extract a clean page name from URL
        $currentPageSlug = $_GET['url'] ?? 'dashboard';
        $pageParts = explode('/', $currentPageSlug);
        $currentPageName = !empty($pageParts[0]) ? ucwords(str_replace(['-', '_'], ' ', $pageParts[0])) : 'Dashboard';
        if (!empty($pageParts[1])) {
            $currentPageName .= ' &middot; ' . ucwords(str_replace(['-', '_'], ' ', $pageParts[1]));
        }
    ?>

    <!-- FLOATING NAV BAR -->
    <nav class="glass-nav">

        <!-- Brand: Logo only -->
        <a href="<?= APP_URL ?>/dashboard" class="nav-brand">
            <img src="<?= APP_URL ?>/Curtiss Logo.png" alt="Curtiss" style="height: 32px; width: 32px; object-fit: contain; display: block;">
        </a>

        <div class="glass-nav-divider"></div>

        <!-- Left Nav Items (Mega Menus) -->
        <div class="glass-nav-left">
            
            <!-- 1. Sales & CRM -->
            <?php 
            $showSalesCRM = hasPermission('crm') || hasPermission('customer') || hasPermission('estimate') || hasPermission('sales') || hasPermission('creditnote') || hasPermission('dunning') || hasPermission('discount') || hasPermission('reptracking') || hasPermission('delivery') || hasPermission('territory');
            if ($showSalesCRM): 
            ?>
            <div class="glass-menu-container">
                <div class="glass-nav-item">Sales & CRM</div>
                <div class="mega-menu">
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Explore</div>
                        <div class="mega-cards-grid">
                            <?php if (hasPermission('crm')): ?>
                            <a href="<?= APP_URL ?>/crm" class="mega-card">
                                <div class="icon"><i class="ph ph-briefcase"></i></div>
                                <div class="mega-card-text">
                                    <div class="title">Leads & CRM</div>
                                    <div class="desc">Manage pipelines</div>
                                </div>
                            </a>
                            <?php endif; ?>
                            <?php if (hasPermission('customer')): ?>
                            <a href="<?= APP_URL ?>/customer" class="mega-card">
                                <div class="icon"><i class="ph ph-users"></i></div>
                                <div class="mega-card-text">
                                    <div class="title">Customer Center</div>
                                    <div class="desc">Client profiles</div>
                                </div>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Billing & AR</div>
                        <?php if (hasPermission('estimate')): ?>
                        <a href="<?= APP_URL ?>/estimate" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-file-text"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Quotes & Estimates</div>
                                <div class="desc">Send tailored pricing</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('sales')): ?>
                        <a href="<?= APP_URL ?>/sales/create" class="mega-list-item text-primary">
                            <div class="icon-wrapper text-primary"><i class="ph ph-pencil-simple"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title text-primary">Billing Creator</div>
                                <div class="desc">Create new Invoices or Sales Orders</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/salesorder" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-list-bullets"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Sales Order Center</div>
                                <div class="desc">Manage standard and route orders</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/sales" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-credit-card"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Invoices & AR</div>
                                <div class="desc">Manage receivables</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('creditnote')): ?>
                        <a href="<?= APP_URL ?>/creditnote" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-money"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Credit Notes</div>
                                <div class="desc">Issue client refunds</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('dunning')): ?>
                        <a href="<?= APP_URL ?>/dunning" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-clock"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Dunning Reminders</div>
                                <div class="desc">Automate follow-ups</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('discount')): ?>
                        <a href="<?= APP_URL ?>/discount" class="mega-list-item text-primary">
                            <div class="icon-wrapper text-primary"><i class="ph ph-tag"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title text-primary">Discount Feed</div>
                                <div class="desc">Configure rules & tiers</div>
                            </div>
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Operations</div>
                        <?php if (hasPermission('reptracking')): ?>
                        <a href="<?php echo APP_URL; ?>/RepTracking/index" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-map-pin"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Master Route Control Panel</div>
                                <div class="desc">Unified route lifecycle & deliveries</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('territory')): ?>
                        <a href="<?= APP_URL ?>/territory" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-map-trifold"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Territory & Routing</div>
                                <div class="desc">Map sales zones</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('sales')): ?>
                        <a href="<?= APP_URL ?>/sales/deleted_list" class="mega-list-item">
                            <div class="icon-wrapper text-danger"><i class="ph ph-trash"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title text-danger">Deleted Invoices</div>
                                <div class="desc">View removed records</div>
                            </div>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
 
            <!-- 2. Supply Chain -->
            <?php 
            $showSupplyChain = hasPermission('inventory') || hasPermission('supplier') || hasPermission('category') || hasPermission('variation') || hasPermission('warehouse') || hasPermission('purchase') || hasPermission('grn') || hasPermission('supplier_return') || hasPermission('expenses');
            if ($showSupplyChain):
            ?>
            <div class="glass-menu-container">
                <div class="glass-nav-item">Supply Chain</div>
                <div class="mega-menu">
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Explore</div>
                        <div class="mega-cards-grid">
                            <?php if (hasPermission('inventory')): ?>
                            <a href="<?= APP_URL ?>/inventory" class="mega-card">
                                <div class="icon"><i class="ph ph-package"></i></div>
                                <div class="mega-card-text">
                                    <div class="title">Products</div>
                                    <div class="desc">Inventory catalog</div>
                                </div>
                            </a>
                            <?php endif; ?>
                            <?php if (hasPermission('supplier')): ?>
                            <a href="<?= APP_URL ?>/supplier" class="mega-card">
                                <div class="icon"><i class="ph ph-factory"></i></div>
                                <div class="mega-card-text">
                                    <div class="title">Supplier Center</div>
                                    <div class="desc">Manage suppliers</div>
                                </div>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Catalog Setup</div>
                        <?php if (hasPermission('category')): ?>
                        <a href="<?= APP_URL ?>/category" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-tag"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Product Categories</div>
                                <div class="desc">Organize your items</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('variation')): ?>
                        <a href="<?= APP_URL ?>/variation" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-sparkle"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Variations</div>
                                <div class="desc">Colors, sizes, types</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('warehouse')): ?>
                        <a href="<?= APP_URL ?>/warehouse" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-buildings"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Warehouse Mgmt</div>
                                <div class="desc">Locations and bins</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/warehouse/transfer" class="mega-list-item text-primary">
                            <div class="icon-wrapper text-primary"><i class="ph ph-arrows-left-right"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title text-primary">Stock Transfer</div>
                                <div class="desc">Move stock between depots</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('inventory')): ?>
                        <a href="<?= APP_URL ?>/inventory/reserved" class="mega-list-item text-primary">
                            <div class="icon-wrapper text-primary"><i class="ph ph-shield-check"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title text-primary">Reserved Stock</div>
                                <div class="desc">View all active stock holds</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/inventory/history" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-chart-bar"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Pricing History</div>
                                <div class="desc">Track cost changes</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/stockledger" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-receipt"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Stock Ledger</div>
                                <div class="desc">Inventory audit trail</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('creditnote')): ?>
                        <a href="<?= APP_URL ?>/creditnote/damaged" class="mega-list-item">
                            <div class="icon-wrapper text-warning"><i class="ph ph-warning-circle"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title text-warning">Damaged Log</div>
                                <div class="desc">Faulty stock reports</div>
                            </div>
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Purchasing</div>
                        <?php if (hasPermission('purchase')): ?>
                        <a href="<?= APP_URL ?>/purchase" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-shopping-cart"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Purchase Orders</div>
                                <div class="desc">Send stock requests</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('grn')): ?>
                        <a href="<?= APP_URL ?>/grn" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-tray-arrow-down"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Goods Receipts (GRN)</div>
                                <div class="desc">Receive inventory</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('supplier_return')): ?>
                        <a href="<?= APP_URL ?>/supplier-return" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-arrow-counter-clockwise"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Supplier Returns</div>
                                <div class="desc">RTV processing</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('expenses')): ?>
                        <a href="<?= APP_URL ?>/expenses" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-receipt"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Expenses & AP</div>
                                <div class="desc">Payable tracking</div>
                            </div>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
 
            <!-- 3. Operations -->
            <?php 
            $showOperations = hasPermission('hrm') || hasPermission('project') || hasPermission('vehicle') || hasPermission('cheque');
            if ($showOperations):
            ?>
            <div class="glass-menu-container">
                <div class="glass-nav-item">Operations</div>
                <div class="mega-menu">
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Human Resources</div>
                        <?php if (hasPermission('hrm')): ?>
                        <a href="<?= APP_URL ?>/hrm" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-user-circle-gear"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">HRM & Employees</div>
                                <div class="desc">Staff directories</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/hrm/payroll" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-bank"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Run Payroll</div>
                                <div class="desc">Process salaries</div>
                            </div>
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Management</div>
                        <?php if (hasPermission('project')): ?>
                        <a href="<?= APP_URL ?>/project" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-clipboard-text"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Projects & Tasks</div>
                                <div class="desc">Team assignments</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('vehicle')): ?>
                        <a href="<?= APP_URL ?>/vehicle" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-car-profile"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Vehicle Management</div>
                                <div class="desc">Fleet maintenance</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('cheque')): ?>
                        <a href="<?= APP_URL ?>/cheque" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-signature"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Cheque Management</div>
                                <div class="desc">Track issuing</div>
                            </div>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
 
            <!-- 4. Accounting -->
            <?php 
            $showAccounting = hasPermission('accounting') || hasPermission('customerpayment') || hasPermission('supplierpayment') || hasPermission('asset');
            if ($showAccounting):
            ?>
            <div class="glass-menu-container">
                <div class="glass-nav-item">Accounting</div>
                <div class="mega-menu">
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Explore</div>
                        <div class="mega-cards-grid">
                            <?php if (hasPermission('accounting')): ?>
                            <a href="<?= APP_URL ?>/accounting/coa" class="mega-card">
                                <div class="icon"><i class="ph ph-notebook"></i></div>
                                <div class="mega-card-text">
                                    <div class="title">Chart of Accts</div>
                                    <div class="desc">General ledger base</div>
                                </div>
                            </a>
                            <a href="<?= APP_URL ?>/accounting/journal" class="mega-card">
                                <div class="icon"><i class="ph ph-pen-nib"></i></div>
                                <div class="mega-card-text">
                                    <div class="title">Journal Entries</div>
                                    <div class="desc">Manual adjustments</div>
                                </div>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Banking & Assets</div>
                        <?php if (hasPermission('accounting')): ?>
                        <a href="<?= APP_URL ?>/banking" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-bank"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Banking & Registers</div>
                                <div class="desc">Accounts and recons</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('customerpayment')): ?>
                        <a href="<?= APP_URL ?>/customerpayment" class="mega-list-item">
                            <div class="icon-wrapper text-primary"><i class="ph ph-hand-coins"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title text-primary">Customer Payments</div>
                                <div class="desc">AR Collections & Allocations</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('supplierpayment')): ?>
                        <a href="<?= APP_URL ?>/supplierpayment" class="mega-list-item">
                            <div class="icon-wrapper text-warning"><i class="ph ph-hand-deposit"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title text-warning">Supplier Payments</div>
                                <div class="desc">AP Payouts & GRN Allocations</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('asset')): ?>
                        <a href="<?= APP_URL ?>/asset" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-buildings"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Fixed Assets Register</div>
                                <div class="desc">Depreciation tracking</div>
                            </div>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
 
            <!-- 5. Analytics -->
            <?php if (hasPermission('report')): ?>
            <div class="glass-menu-container align-right">
                <div class="glass-nav-item">Analytics</div>
                <div class="mega-menu">
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Insights</div>
                        <a href="<?= APP_URL ?>/report" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-chart-line-up"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Financial Reports Hub</div>
                                <div class="desc">Statements & summaries</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/budget" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-target"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Budgets vs Actuals</div>
                                <div class="desc">Performance tracking</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
 
            <!-- 5.5 E-Commerce Operations -->
            <?php if (hasPermission('ecommerce')): ?>
            <div class="glass-menu-container align-right">
                <div class="glass-nav-item">E-Commerce</div>
                <div class="mega-menu">
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Wholesaler Management</div>
                        <a href="<?= APP_URL ?>/ecommerce/requests" class="mega-list-item">
                            <div class="icon-wrapper text-primary"><i class="ph ph-handshake"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title text-primary">Wholesaler Requests</div>
                                <div class="desc">Approve, decline and link requests</div>
                            </div>
                        </a>
                    </div>
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Retail Directory</div>
                        <a href="<?= APP_URL ?>/ecommerce/retail" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-users"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Retail Customers</div>
                                <div class="desc">View e-commerce retail accounts</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
 
            <!-- 6. Admin (Conditional) -->
            <?php 
            $showAdmin = hasPermission('settings') || hasPermission('user') || hasPermission('tax') || hasPermission('paymentterm') || hasPermission('audit');
            if ($showAdmin):
            ?>
            <div class="glass-menu-container align-right">
                <div class="glass-nav-item">Admin</div>
                <div class="mega-menu">
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Explore</div>
                        <div class="mega-cards-grid">
                            <?php if (hasPermission('settings')): ?>
                            <a href="<?= APP_URL ?>/settings" class="mega-card">
                                <div class="icon"><i class="ph ph-gear"></i></div>
                                <div class="mega-card-text">
                                    <div class="title">Settings</div>
                                    <div class="desc">Company config</div>
                                </div>
                            </a>
                            <a href="<?= APP_URL ?>/release" class="mega-card">
                                <div class="icon"><i class="ph ph-upload-simple"></i></div>
                                <div class="mega-card-text">
                                    <div class="title">App Releases</div>
                                    <div class="desc">APK updates</div>
                                </div>
                            </a>
                            <?php endif; ?>
                            <?php if (hasPermission('user')): ?>
                            <a href="<?= APP_URL ?>/user" class="mega-card">
                                <div class="icon"><i class="ph ph-lock-key"></i></div>
                                <div class="mega-card-text">
                                    <div class="title">Users</div>
                                    <div class="desc">Roles & access</div>
                                </div>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Compliance</div>
                        <?php if (hasPermission('tax')): ?>
                        <a href="<?= APP_URL ?>/tax" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-scales"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Tax Rates & Rules</div>
                                <div class="desc">Manage VAT/GST</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('paymentterm')): ?>
                        <a href="<?= APP_URL ?>/paymentterm" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-handshake"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Payment Terms</div>
                                <div class="desc">Standard & Date-Driven</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('accounting')): ?>
                        <a href="<?= APP_URL ?>/accounting/close_year" class="mega-list-item">
                            <div class="icon-wrapper text-danger"><i class="ph ph-lock"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title text-danger">Close Financial Year</div>
                                <div class="desc">Lock historical data</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('audit')): ?>
                        <a href="<?= APP_URL ?>/audit" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-shield-check"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">System Audit Trail</div>
                                <div class="desc">Monitor user activity</div>
                            </div>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div><!-- /.glass-nav-left -->

        <!-- Page Heading (pushes to right) -->
        <div class="nav-page-heading">
            <span class="nav-page-dot"></span>
            <span class="nav-page-title"><?= htmlspecialchars($currentPageTitle) ?></span>
        </div>

    </nav>

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

    <!-- GLASSMORPHISM RECENT BAR -->
    <div id="recentBar" class="mac-recent-bar">
        <div class="recent-toggle-tab" onclick="toggleRecentBar()">
            <span id="recentToggleIcon"><i class="ph ph-clock-counter-clockwise"></i></span>
            Recent
        </div>
        <div class="recent-links-container" id="recentLinksContainer">
            <!-- JavaScript will populate this dynamically -->
        </div>
        <div class="recent-clock">
            <i class="ph ph-clock" style="font-size: 13px;"></i>
            <span id="clock"></span>
        </div>
    </div>

    <script>
        // --- Session Expiration and Inactivity Monitoring ---
        let sessionExpiredHandled = false;
        function handleSessionExpiration() {
            if (sessionExpiredHandled) return;
            sessionExpiredHandled = true;

            // Save the current location (path + query parameters)
            const currentUrl = window.location.pathname + window.location.search;
            sessionStorage.setItem('redirect_url', currentUrl);

            // Clear local session variables
            sessionStorage.removeItem('curtiss_history');
            sessionStorage.removeItem('report_breadcrumbs');

            alert('Session Expired\n\nPlease login again.');
            window.location.href = '<?= APP_URL ?>/auth/login';
        }

        // Global AJAX / API Interceptors
        // 1. Intercept Fetch API
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            return originalFetch.apply(this, args).then(response => {
                if (response.status === 401 || response.status === 419 || response.status === 403) {
                    handleSessionExpiration();
                }
                return response;
            });
        };

        // 2. Intercept XMLHttpRequest (AJAX)
        const originalOpen = XMLHttpRequest.prototype.open;
        const originalSend = XMLHttpRequest.prototype.send;
        XMLHttpRequest.prototype.open = function(method, url, ...args) {
            this._url = url;
            return originalOpen.apply(this, [method, url, ...args]);
        };
        XMLHttpRequest.prototype.send = function(...args) {
            const onreadystatechange = this.onreadystatechange;
            this.onreadystatechange = function() {
                if (this.readyState === 4) {
                    if (this.status === 401 || this.status === 419 || this.status === 403) {
                        handleSessionExpiration();
                    }
                }
                if (onreadystatechange) {
                    return onreadystatechange.apply(this, arguments);
                }
            };
            return originalSend.apply(this, args);
        };

        // 3. Inactivity Timeout (15 Minutes)
        let inactivityTimeout;
        const TIMEOUT_DURATION = 15 * 60 * 1000; // 15 minutes
        function resetInactivityTimer() {
            if (sessionExpiredHandled) return;
            clearTimeout(inactivityTimeout);
            inactivityTimeout = setTimeout(handleInactivityTimeout, TIMEOUT_DURATION);
        }
        function handleInactivityTimeout() {
            originalFetch('<?= APP_URL ?>/auth/timeout_logout')
                .finally(() => {
                    handleSessionExpiration();
                });
        }
        window.addEventListener('load', resetInactivityTimer);
        document.addEventListener('mousemove', resetInactivityTimer);
        document.addEventListener('keypress', resetInactivityTimer);
        document.addEventListener('click', resetInactivityTimer);
        document.addEventListener('scroll', resetInactivityTimer);
        document.addEventListener('touchstart', resetInactivityTimer);

        // 4. Browser Multi-Tab Check (Poller runs every 10 seconds)
        setInterval(() => {
            if (sessionExpiredHandled) return;
            originalFetch('<?= APP_URL ?>/auth/check_session')
                .then(res => res.json())
                .then(data => {
                    if (!data.logged_in) {
                        handleSessionExpiration();
                    }
                })
                .catch(err => {
                    // Ignore transient network errors
                });
        }, 10000);

        // Clear redirect_url if we are currently on the page that was successfully loaded
        const loadCheckUrl = window.location.pathname + window.location.search;
        if (sessionStorage.getItem('redirect_url') === loadCheckUrl) {
            sessionStorage.removeItem('redirect_url');
        }

        // Clock Logic
        function updateClock() {
            const now = new Date();
            const options = { weekday: 'short', hour: 'numeric', minute: '2-digit' };
            document.getElementById('clock').innerText = now.toLocaleDateString('en-US', options).replace(',', '');
        }
        setInterval(updateClock, 1000);
        updateClock();

        // --- Recent History Tracker Logic ---
        document.addEventListener("DOMContentLoaded", function() {
            const currentUrl = window.location.href;
            const currentTitle = "<?= addslashes($data['title'] ?? 'Dashboard') ?>";
            
            // Exclude the login/logout pages from history
            if(currentUrl.includes('/auth/')) return;

            // Fetch existing history from Session Storage
            let history = JSON.parse(sessionStorage.getItem('curtiss_history')) || [];
            
            // Remove current URL if it already exists in the array
            history = history.filter(item => item.url !== currentUrl);
            
            // Add the current page to the absolute front of the array
            history.unshift({ url: currentUrl, title: currentTitle });
            
            // Keep a maximum of 8 recent pages
            if(history.length > 8) history.pop();
            
            // Save it back to session storage
            sessionStorage.setItem('curtiss_history', JSON.stringify(history));

            // Render the pills to the HTML bar (skipping index 0)
            const container = document.getElementById('recentLinksContainer');
            if (history.length <= 1) {
                container.innerHTML = '<span style="font-size:12px; color:var(--text-muted);">No recent pages yet.</span>';
            } else {
                for(let i = 1; i < history.length; i++) {
                    let a = document.createElement('a');
                    a.href = history[i].url;
                    a.className = 'recent-link';
                    a.innerHTML = `<i class="ph ph-file-text" style="font-size: 13px;"></i> ${history[i].title}`;
                    container.appendChild(a);
                }
            }

            // Restore the Collapse state from local storage
            const bar = document.getElementById('recentBar');
            const icon = document.getElementById('recentToggleIcon');
            let isCollapsed = localStorage.getItem('curtiss_recent_collapsed') === 'true';
            
            if(isCollapsed) {
                bar.classList.add('collapsed');
                icon.innerHTML = '<i class="ph ph-clock"></i>';
            }
        });

        function toggleRecentBar() {
            const bar = document.getElementById('recentBar');
            const icon = document.getElementById('recentToggleIcon');
            
            bar.classList.toggle('collapsed');
            const collapsedNow = bar.classList.contains('collapsed');
            
            // Save preference to local storage
            localStorage.setItem('curtiss_recent_collapsed', collapsedNow);
            icon.innerHTML = collapsedNow ? '<i class="ph ph-clock"></i>' : '<i class="ph ph-clock-counter-clockwise"></i>';
        }
    </script>
    
    <?php include '../app/Views/layouts/resilient_loader.php'; ?>
</body>
</html>