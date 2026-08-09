<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curtiss ERP &mdash; Sign In</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;0,14..32,900;1,14..32,400&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent: #2563eb;
            --accent-light: #3b82f6;
            --accent-glow: rgba(37,99,235,0.18);
            --accent-hover: #1d4ed8;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --card-bg: rgba(255,255,255,0.75);
            --card-border: rgba(148,163,184,0.25);
            --error-text: #dc2626;
            --input-border: #e2e8f0;
            --input-focus: #2563eb;
            --surface: #ffffff;
        }

        html, body {
            width: 100%; min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f0f4ff;
            color: var(--text-primary);
            overflow-x: hidden;
        }

        /* ===================== ANIMATED MESH BG ===================== */
        .mesh-bg {
            position: fixed; inset: 0; z-index: 0;
            background:
                radial-gradient(ellipse 70% 50% at 20% 20%, rgba(147,197,253,0.35) 0%, transparent 60%),
                radial-gradient(ellipse 60% 60% at 80% 80%, rgba(196,181,253,0.25) 0%, transparent 60%),
                radial-gradient(ellipse 80% 40% at 60% 10%, rgba(186,230,253,0.3) 0%, transparent 55%),
                linear-gradient(160deg, #e0eaff 0%, #f5f3ff 40%, #eff6ff 70%, #f0fdf4 100%);
            animation: mesh-shift 12s ease-in-out infinite alternate;
        }
        @keyframes mesh-shift {
            0%   { filter: hue-rotate(0deg) brightness(1); }
            100% { filter: hue-rotate(8deg) brightness(1.03); }
        }

        /* Floating shapes */
        .shape {
            position: fixed; border-radius: 50%; z-index: 0;
            filter: blur(60px); pointer-events: none; opacity: 0;
            animation: shape-float linear infinite;
        }
        @keyframes shape-float {
            0%   { transform: translateY(110vh) scale(0.6); opacity: 0; }
            10%  { opacity: 0.6; }
            90%  { opacity: 0.5; }
            100% { transform: translateY(-20vh) scale(1.1); opacity: 0; }
        }

        /* Grid overlay */
        .grid-overlay {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background-image:
                linear-gradient(rgba(37,99,235,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(37,99,235,0.04) 1px, transparent 1px);
            background-size: 44px 44px;
        }

        /* ===================== SPLASH ===================== */
        #splashScreen {
            position: fixed; inset: 0; z-index: 9999;
            background: #ffffff;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 0;
            transition: opacity 0.8s cubic-bezier(0.4,0,0.2,1), visibility 0.8s;
        }
        #splashScreen.hidden { opacity: 0; visibility: hidden; pointer-events: none; }
        #lottie-container { width: 340px; height: 340px; }

        /* ===================== APP LAYOUT ===================== */
        .app-wrap {
            position: relative; z-index: 10;
            min-height: 100vh;
            display: flex; flex-direction: column;
            align-items: center; padding: 48px 24px 24px;
        }

        /* ===================== LOGO / HEADER ===================== */
        .site-header {
            display: flex; flex-direction: column; align-items: center;
            margin-bottom: 48px;
            opacity: 0; transform: translateY(-24px);
            transition: opacity 0.55s ease, transform 0.55s ease;
        }
        .site-header.visible { opacity: 1; transform: translateY(0); }

        .logo-lockup {
            display: flex; align-items: center; gap: 12px; margin-bottom: 6px;
        }
        .logo-mark {
            width: 44px; height: 44px; border-radius: 10px;
            overflow: hidden; background: #ffffff;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 14px rgba(0,0,0,0.12);
            border: 1px solid rgba(0,0,0,0.08);
        }
        .logo-mark img {
            width: 36px; height: 36px; object-fit: contain;
        }
        .logo-name {
            font-size: 20px; font-weight: 800; letter-spacing: -0.4px;
            color: var(--text-primary);
        }
        .logo-name span { color: var(--accent); }

        .screen-tagline { display: none; }

        /* ===================== SCREEN WRAPPER ===================== */
        .screen-container { width: 100%; max-width: 1400px; margin: 0 auto; }

        .screen {
            display: none;
            animation: none;
        }
        .screen.entering {
            display: block;
            animation: screen-enter 0.5s cubic-bezier(0.34,1.56,0.64,1) forwards;
        }
        .screen.leaving {
            display: block;
            animation: screen-leave 0.3s ease forwards;
            pointer-events: none;
        }
        @keyframes screen-enter {
            from { opacity: 0; transform: translateY(28px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes screen-leave {
            from { opacity: 1; transform: translateY(0) scale(1); }
            to   { opacity: 0; transform: translateY(-16px) scale(0.98); }
        }

        /* ===================== WHO ARE YOU SCREEN ===================== */
        .who-heading {
            text-align: center; margin-bottom: 40px;
        }
        .who-heading h1 {
            font-size: clamp(28px, 4vw, 40px); font-weight: 800;
            letter-spacing: -1.5px; color: var(--text-primary);
            line-height: 1.1;
        }
        .who-heading h1 span {
            background: linear-gradient(120deg, #2563eb 0%, #7c3aed 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .who-heading p {
            margin-top: 10px; font-size: 15px; color: var(--text-secondary);
        }

        /* Alerts */
        .alert-zone { max-width: 640px; margin: 0 auto 28px; }
        .alert {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 13px 16px; border-radius: 12px;
            font-size: 13px; font-weight: 500; line-height: 1.5;
            margin-bottom: 10px;
        }
        .alert-error {
            background: #fef2f2; border: 1px solid rgba(220,38,38,0.2); color: #dc2626;
        }
        .alert-warning {
            background: #fffbeb; border: 1px solid rgba(245,158,11,0.2); color: #b45309;
        }

        /* User Cards — single horizontal scrollable row */
        .user-grid-wrap {
            width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            padding-bottom: 12px;
            /* hide scrollbar but keep functionality */
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .user-grid-wrap::-webkit-scrollbar { display: none; }

        .user-grid {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            gap: 20px;
            /* center when cards fit; scroll when they overflow */
            width: max-content;
            min-width: 100%;
            justify-content: center;
            padding: 4px 4px 4px 4px;
        }

        /* Per-card width adapts to viewport */
        .user-card {
            flex: 1 1 200px;
            max-width: 240px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 32px 20px 24px;
            display: flex; flex-direction: column; align-items: center;
            text-align: center; cursor: pointer;
            position: relative; overflow: hidden;
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.04), 0 0 0 1px rgba(255,255,255,0.6) inset;
            opacity: 0; transform: translateY(30px) scale(0.95);
            transition: transform 0.25s cubic-bezier(0.34,1.56,0.64,1),
                        box-shadow 0.25s ease, border-color 0.25s ease,
                        background 0.25s ease;
        }
        .user-card.animated {
            opacity: 1; transform: translateY(0) scale(1);
        }
        .user-card::before {
            content: '';
            position: absolute; inset: 0; border-radius: 20px;
            background: linear-gradient(135deg, rgba(37,99,235,0.06) 0%, rgba(124,58,237,0.04) 100%);
            opacity: 0; transition: opacity 0.25s ease;
        }
        .user-card:hover::before { opacity: 1; }
        .user-card:hover {
            transform: translateY(-6px) scale(1.02);
            border-color: rgba(37,99,235,0.35);
            box-shadow:
                0 12px 40px rgba(37,99,235,0.12),
                0 4px 16px rgba(0,0,0,0.06),
                0 0 0 1px rgba(255,255,255,0.8) inset;
        }
        .user-card:active { transform: translateY(-2px) scale(1.01); }

        .user-avatar {
            width: 76px; height: 76px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; font-weight: 800; color: white;
            margin-bottom: 16px; position: relative;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        /* Pulse ring on hover */
        .user-card:hover .user-avatar::after {
            content: '';
            position: absolute; inset: -5px; border-radius: 50%;
            border: 2px solid currentColor;
            opacity: 0.25; animation: pulse-ring 1.2s ease-out infinite;
        }
        @keyframes pulse-ring {
            from { transform: scale(0.9); opacity: 0.35; }
            to   { transform: scale(1.2); opacity: 0; }
        }

        /* Avatar gradient per role */
        .avatar-admin     { background: linear-gradient(135deg, #2563eb, #1e40af); }
        .avatar-accountant { background: linear-gradient(135deg, #7c3aed, #5b21b6); }
        .avatar-staff     { background: linear-gradient(135deg, #0891b2, #0e7490); }
        .avatar-default   { background: linear-gradient(135deg, #475569, #334155); }

        .user-fullname {
            font-size: 15px; font-weight: 700; color: var(--text-primary);
            margin-bottom: 8px; line-height: 1.2;
        }
        .role-badge {
            display: inline-flex; align-items: center;
            padding: 4px 12px; border-radius: 99px;
            font-size: 11px; font-weight: 600; letter-spacing: 0.3px;
        }
        .badge-admin     { background: rgba(37,99,235,0.1);  color: #1d4ed8; }
        .badge-accountant { background: rgba(124,58,237,0.1); color: #6d28d9; }
        .badge-staff     { background: rgba(8,145,178,0.1);  color: #0e7490; }
        .badge-default   { background: rgba(71,85,105,0.1);  color: #475569; }

        .card-arrow {
            position: absolute; top: 14px; right: 16px;
            width: 28px; height: 28px; border-radius: 50%;
            background: rgba(37,99,235,0.08);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transform: scale(0.7);
            transition: opacity 0.2s ease, transform 0.2s cubic-bezier(0.34,1.56,0.64,1);
            color: var(--accent);
        }
        .user-card:hover .card-arrow { opacity: 1; transform: scale(1); }

        /* ===================== PASSWORD SCREEN ===================== */
        .auth-stage {
            max-width: 440px; margin: 0 auto;
        }

        .user-showcase {
            text-align: center; margin-bottom: 36px;
        }
        .showcase-avatar {
            width: 88px; height: 88px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 34px; font-weight: 800; color: white;
            margin: 0 auto 16px;
            box-shadow: 0 12px 36px rgba(37,99,235,0.25);
            animation: pop-in 0.5s cubic-bezier(0.34,1.56,0.64,1);
        }
        @keyframes pop-in {
            from { transform: scale(0.5); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }
        .showcase-name {
            font-size: 24px; font-weight: 800; letter-spacing: -0.5px;
            color: var(--text-primary); margin-bottom: 6px;
        }
        .showcase-role {
            font-size: 13px; font-weight: 500; color: var(--text-secondary);
        }

        .auth-card {
            background: rgba(255,255,255,0.85);
            border: 1px solid var(--card-border);
            border-radius: 24px; padding: 36px;
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            box-shadow:
                0 20px 60px rgba(37,99,235,0.08),
                0 4px 16px rgba(0,0,0,0.04),
                0 0 0 1px rgba(255,255,255,0.8) inset;
        }

        /* Field styles */
        .field { margin-bottom: 22px; }
        .field-label {
            display: flex; align-items: center; justify-content: space-between;
            font-size: 13px; font-weight: 600; color: var(--text-primary);
            margin-bottom: 10px;
        }
        .field-input-wrap { position: relative; }
        .field-input {
            width: 100%; padding: 15px 48px 15px 18px;
            background: #f8fafc; border: 1.5px solid var(--input-border);
            border-radius: 14px; color: var(--text-primary);
            font-family: inherit; font-size: 15px; outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }
        .field-input::placeholder { color: var(--text-muted); }
        .field-input:focus {
            border-color: var(--input-focus);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(37,99,235,0.1);
        }
        .field-input.has-error { border-color: #ef4444; }
        .field-error { font-size: 12px; color: var(--error-text); margin-top: 6px; display: block; }

        .pwd-toggle {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--text-muted); display: flex; align-items: center;
            padding: 4px; transition: color 0.2s ease;
        }
        .pwd-toggle:hover { color: var(--text-secondary); }

        /* Submit Button */
        .btn-submit {
            width: 100%; padding: 15px;
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            color: white; border: none; border-radius: 14px;
            font-family: inherit; font-size: 15px; font-weight: 700;
            cursor: pointer; letter-spacing: 0.2px;
            position: relative; overflow: hidden;
            transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
            box-shadow: 0 6px 24px rgba(37,99,235,0.3);
        }
        .btn-submit::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(rgba(255,255,255,0.12) 0%, transparent 100%);
        }
        .btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 32px rgba(37,99,235,0.4);
        }
        .btn-submit:active:not(:disabled) { transform: translateY(0); }
        .btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }

        /* Ripple on click */
        .btn-submit .ripple {
            position: absolute; border-radius: 50%;
            background: rgba(255,255,255,0.4);
            transform: scale(0); animation: ripple-out 0.6s linear;
            pointer-events: none;
        }
        @keyframes ripple-out {
            to { transform: scale(4); opacity: 0; }
        }

        /* Back link */
        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            margin-top: 22px; font-size: 14px; font-weight: 500;
            color: var(--text-secondary); background: none; border: none;
            cursor: pointer; transition: color 0.2s ease;
            text-decoration: none;
        }
        .back-link:hover { color: var(--text-primary); }
        .back-link-wrap { text-align: center; }

        /* Dev credit */
        .dev-credit {
            margin-top: 40px; text-align: center;
            font-size: 12px; color: var(--text-muted);
        }
        .dev-credit a {
            color: var(--text-secondary); font-weight: 600;
            text-decoration: none; transition: color 0.2s;
        }
        .dev-credit a:hover { color: var(--accent); }

        /* Shimmer loading state */
        @keyframes shimmer {
            from { background-position: -400px 0; }
            to   { background-position: 400px 0; }
        }
        .skeleton {
            border-radius: 20px; height: 180px;
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 400px 100%;
            animation: shimmer 1.5s infinite linear;
        }

        /* ===================== RESPONSIVE ===================== */
        @media (max-width: 640px) {
            .app-wrap { padding: 32px 16px 20px; }
            .user-grid { grid-template-columns: repeat(2, 1fr); gap: 14px; }
            .who-heading h1 { font-size: 26px; }
            .auth-card { padding: 28px 22px; }
        }
    </style>
</head>
<body>

<!-- Animated background -->
<div class="mesh-bg"></div>
<div class="grid-overlay"></div>

<!-- Floating shapes (generated by JS) -->
<div id="floatingShapes"></div>

<!-- Splash Screen -->
<div id="splashScreen">
    <div id="lottie-container"></div>
</div>

<!-- App -->
<div class="app-wrap" id="appWrap">

    <!-- Header -->
    <header class="site-header" id="siteHeader">
        <div class="logo-lockup">
            <div class="logo-mark">
                <img src="<?= APP_URL ?>/favicon.ico" alt="Curtiss Logo" style="width: 24px; height: 24px;">
            </div>
            <div class="logo-name">CURTISS <span>ERP</span></div>
        </div>
    </header>

    <!-- Alert zone -->
    <div class="alert-zone" id="alertZone" style="width:100%;max-width:1400px;">
        <?php if(!empty($data['lockout_err'])): ?>
        <div class="alert alert-error">&#9888;&nbsp; <?= $data['lockout_err'] ?></div>
        <?php endif; ?>
        <?php if(!empty($data['csrf_err'])): ?>
        <div class="alert alert-warning">&#9889;&nbsp; <?= $data['csrf_err'] ?></div>
        <?php endif; ?>
        <?php if(!empty($data['system_err'])): ?>
        <div class="alert alert-error">&#10005;&nbsp; <strong>System Error:</strong> <?= htmlspecialchars($data['system_err']) ?></div>
        <?php endif; ?>
    </div>

    <div class="screen-container">

        <!-- SCREEN 1: User selection -->
        <div class="screen" id="screenSelect">
            <div class="who-heading">
                <h1>Who are <span>you?</span></h1>
                <p>Select your account below to sign in securely</p>
            </div>
            <div class="user-grid-wrap"><div class="user-grid" id="userGrid">
                <?php
                $eligible_users = $data['eligible_users'] ?? [];
                if (empty($eligible_users)):
                ?>
                    <div style="grid-column:1/-1;text-align:center;padding:60px 0;color:var(--text-secondary);">
                        No eligible accounts found. Contact your administrator.
                    </div>
                <?php else: ?>
                    <?php foreach ($eligible_users as $u):
                        $roleRaw = strtolower($u->role ?? '');
                        if (strpos($roleRaw, 'admin') !== false) {
                            $avatarClass = 'avatar-admin';
                            $badgeClass  = 'badge-admin';
                        } elseif (strpos($roleRaw, 'account') !== false) {
                            $avatarClass = 'avatar-accountant';
                            $badgeClass  = 'badge-accountant';
                        } elseif (strpos($roleRaw, 'office') !== false || strpos($roleRaw, 'staff') !== false) {
                            $avatarClass = 'avatar-staff';
                            $badgeClass  = 'badge-staff';
                        } else {
                            $avatarClass = 'avatar-default';
                            $badgeClass  = 'badge-default';
                        }
                        $initials = strtoupper(substr($u->full_name ?? $u->username, 0, 1));
                    ?>
                    <div class="user-card"
                         onclick="selectUser('<?= htmlspecialchars($u->username, ENT_QUOTES) ?>', '<?= htmlspecialchars($u->full_name ?? $u->username, ENT_QUOTES) ?>', '<?= htmlspecialchars($u->role, ENT_QUOTES) ?>', '<?= $avatarClass ?>', '<?= $badgeClass ?>')">
                        <div class="card-arrow">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </div>
                        <div class="user-avatar <?= $avatarClass ?>"><?= $initials ?></div>
                        <div class="user-fullname"><?= htmlspecialchars($u->full_name ?? $u->username) ?></div>
                        <span class="role-badge <?= $badgeClass ?>"><?= htmlspecialchars($u->role) ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div></div><!-- /user-grid-wrap -->
        </div>

        <!-- SCREEN 2: Password -->
        <div class="screen" id="screenPassword">
            <div class="auth-stage">
                <div class="user-showcase">
                    <div class="showcase-avatar avatar-default" id="showcaseAvatar">U</div>
                    <div class="showcase-name" id="showcaseName">User Name</div>
                    <div class="showcase-role" id="showcaseRole">Role</div>
                </div>

                <div class="auth-card">
                    <?php if(!empty($data['username_err'])): ?>
                    <div class="alert alert-error" style="margin-bottom:18px;">
                        <?= $data['username_err'] ?>
                    </div>
                    <?php endif; ?>

                    <form action="<?= APP_URL ?>/auth/login" method="POST" id="loginForm">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="username" id="formUsername" value="<?= isset($data['username']) ? htmlspecialchars($data['username']) : '' ?>">

                        <div class="field">
                            <label class="field-label" for="password">
                                <span>Password</span>
                            </label>
                            <div class="field-input-wrap">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="field-input <?= !empty($data['password_err']) ? 'has-error' : '' ?>"
                                    placeholder="Enter your password"
                                    autocomplete="current-password"
                                    <?= !empty($data['lockout_err']) ? 'disabled' : '' ?>
                                >
                                <button type="button" class="pwd-toggle" onclick="togglePwd()" aria-label="Toggle password">
                                    <svg id="eyeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                            <?php if(!empty($data['password_err'])): ?>
                            <span class="field-error"><?= $data['password_err'] ?></span>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn-submit" id="submitBtn" <?= !empty($data['lockout_err']) ? 'disabled' : '' ?>>
                            Authorize &amp; Sign In
                        </button>
                    </form>
                </div>

                <div class="back-link-wrap">
                    <button class="back-link" onclick="goBack()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"/>
                            <polyline points="12 19 5 12 12 5"/>
                        </svg>
                        That's not me &mdash; go back
                    </button>
                </div>
            </div>
        </div>

    </div><!-- /screen-container -->

    <div class="dev-credit">Powered by <a href="https://suzxlabs.com" target="_blank" rel="noopener">suzxlabs</a></div>
</div>

<script>
/* ============================================================
   DATA FROM PHP
   ============================================================ */
const hasErrors  = <?= (!empty($data['lockout_err']) || !empty($data['csrf_err']) || !empty($data['system_err']) || !empty($data['username_err']) || !empty($data['password_err'])) ? 'true' : 'false' ?>;
const prevUser   = <?= json_encode($data['username'] ?? '') ?>;
const usersData  = <?= json_encode($data['eligible_users'] ?? []) ?>;

/* ============================================================
   FLOATING SHAPES
   ============================================================ */
(function spawnShapes() {
    const colors = [
        'rgba(147,197,253,0.55)',
        'rgba(196,181,253,0.45)',
        'rgba(186,230,253,0.5)',
        'rgba(167,243,208,0.4)',
        'rgba(253,186,116,0.3)',
    ];
    const container = document.getElementById('floatingShapes');
    for (let i = 0; i < 8; i++) {
        const el = document.createElement('div');
        el.className = 'shape';
        const size = 80 + Math.random() * 160;
        el.style.cssText = [
            'width:' + size + 'px',
            'height:' + size + 'px',
            'left:' + (Math.random() * 95) + '%',
            'background:' + colors[Math.floor(Math.random() * colors.length)],
            'animation-duration:' + (18 + Math.random() * 22) + 's',
            'animation-delay:' + (-Math.random() * 20) + 's',
        ].join(';');
        container.appendChild(el);
    }
})();

/* ============================================================
   SCREEN MANAGEMENT
   ============================================================ */
const screenSelect   = document.getElementById('screenSelect');
const screenPassword = document.getElementById('screenPassword');
const siteHeader     = document.getElementById('siteHeader');

const alertZone      = document.getElementById('alertZone');

function showScreen(newId) {
    const screens = [screenSelect, screenPassword];
    const newScreen = document.getElementById(newId);

    screens.forEach(s => {
        if (s !== newScreen && s.classList.contains('entering')) {
            s.classList.remove('entering');
            s.classList.add('leaving');
            setTimeout(() => {
                s.classList.remove('leaving');
                s.style.display = 'none';
            }, 300);
        }
    });

    newScreen.style.display = 'block';
    newScreen.classList.remove('leaving');
    void newScreen.offsetWidth; // reflow
    newScreen.classList.add('entering');
}

/* ============================================================
   USER CARDS — STAGGERED ANIMATION
   ============================================================ */
function animateCards() {
    const cards = document.querySelectorAll('.user-card');
    cards.forEach((card, i) => {
        setTimeout(() => card.classList.add('animated'), 80 + i * 70);
    });
}

/* ============================================================
   SELECT USER
   ============================================================ */
function selectUser(username, fullName, role, avatarClass, badgeClass) {
    // Populate hidden form
    document.getElementById('formUsername').value = username;

    // Populate showcase
    const avatar = document.getElementById('showcaseAvatar');
    avatar.className = 'showcase-avatar ' + avatarClass;
    avatar.textContent = fullName.charAt(0).toUpperCase();

    document.getElementById('showcaseName').textContent = fullName;
    document.getElementById('showcaseRole').textContent = role;

    // Hide global alerts (they will show in card)
    alertZone.style.display = 'none';

    taglineText.textContent = 'Enter your password to authorize';

    showScreen('screenPassword');
    setTimeout(() => {
        const pwd = document.getElementById('password');
        if (pwd && !pwd.disabled) pwd.focus();
    }, 500);
}

function goBack() {
    document.getElementById('formUsername').value = '';
    document.getElementById('password').value = '';
    taglineText.textContent = 'Identify yourself to continue';
    alertZone.style.display = 'none';
    showScreen('screenSelect');
}

/* ============================================================
   INIT
   ============================================================ */
function initApp() {
    siteHeader.classList.add('visible');

    if (hasErrors && prevUser) {
        // Restore the previously selected user state
        let fullName = prevUser;
        let role = '';
        let avatarClass = 'avatar-default';
        let badgeClass = 'badge-default';

        const userObj = usersData.find(function(u) { return u.username === prevUser; });
        if (userObj) {
            fullName    = userObj.full_name || prevUser;
            role        = userObj.role || '';
            const r = role.toLowerCase();
            if (r.indexOf('admin') !== -1)   { avatarClass = 'avatar-admin';      badgeClass = 'badge-admin'; }
            else if (r.indexOf('account') !== -1) { avatarClass = 'avatar-accountant'; badgeClass = 'badge-accountant'; }
            else if (r.indexOf('office') !== -1 || r.indexOf('staff') !== -1) { avatarClass = 'avatar-staff'; badgeClass = 'badge-staff'; }
        }

        alertZone.style.display = 'block';
        selectUser(prevUser, fullName, role, avatarClass, badgeClass);
    } else {
        showScreen('screenSelect');
        setTimeout(animateCards, 200);
    }
}

/* ============================================================
   SPLASH + BOOT
   ============================================================ */
const splashScreen = document.getElementById('splashScreen');

if (hasErrors) {
    splashScreen.style.display = 'none';
    initApp();
} else {
    lottie.loadAnimation({
        container: document.getElementById('lottie-container'),
        renderer: 'svg', loop: true, autoplay: true,
        path: '<?= APP_URL ?>/assets/lottie/scene.json'
    });
    setTimeout(function() {
        splashScreen.classList.add('hidden');
        setTimeout(initApp, 500);
    }, 3000);
}

/* ============================================================
   PASSWORD TOGGLE
   ============================================================ */
function togglePwd() {
    var pwd  = document.getElementById('password');
    var icon = document.getElementById('eyeIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        pwd.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}

/* ============================================================
   SUBMIT — ripple + loading state
   ============================================================ */
document.getElementById('loginForm').addEventListener('submit', function(e) {
    var btn = document.getElementById('submitBtn');
    // Ripple
    var rect = btn.getBoundingClientRect();
    var size = Math.max(rect.width, rect.height);
    var ripple = document.createElement('span');
    ripple.className = 'ripple';
    ripple.style.cssText = 'width:' + size + 'px;height:' + size + 'px;top:' + (e.clientY - rect.top - size/2) + 'px;left:' + (e.clientX - rect.left - size/2) + 'px;';
    btn.appendChild(ripple);
    setTimeout(function() { ripple.remove(); }, 700);
    btn.textContent = 'Authorizing\u2026';
    btn.disabled = true;
});

/* ============================================================
   REDIRECT URL RESTORE
   ============================================================ */
document.addEventListener('DOMContentLoaded', function() {
    var redirectUrl = sessionStorage.getItem('redirect_url');
    if (redirectUrl) {
        var form  = document.getElementById('loginForm');
        var input = form.querySelector('input[name="redirect_url"]');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden'; input.name = 'redirect_url';
            form.appendChild(input);
        }
        input.value = redirectUrl;
    }
});
</script>

<?php if(isset($data['debug_console'])): ?>
<script>console.error("ERP DEBUG LOG: <?= $data['debug_console'] ?>");</script>
<?php endif; ?>

</body>
</html>