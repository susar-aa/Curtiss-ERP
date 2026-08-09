<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curtiss ERP &mdash; Sign In</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-deep: #05070f;
            --accent: #4f8ef7;
            --accent-glow: rgba(79,142,247,0.25);
            --glass-bg: rgba(255,255,255,0.04);
            --glass-border: rgba(255,255,255,0.08);
            --text-primary: #f0f4ff;
            --text-secondary: #7a88a8;
            --text-muted: #4a566d;
            --error-bg: rgba(255,77,77,0.08);
            --warning-bg: rgba(245,166,35,0.08);
            --input-bg: rgba(255,255,255,0.03);
            --input-border: rgba(255,255,255,0.07);
            --input-focus-border: rgba(79,142,247,0.6);
            --input-focus-glow: rgba(79,142,247,0.12);
        }

        html, body {
            width: 100%; height: 100%;
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--bg-deep);
            color: var(--text-primary);
            overflow: hidden;
        }

        /* Animated background */
        .bg-canvas {
            position: fixed; inset: 0; z-index: 0;
            background:
                radial-gradient(ellipse 80% 60% at 70% 20%, rgba(79,142,247,0.08) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 20% 80%, rgba(100,60,200,0.06) 0%, transparent 60%),
                linear-gradient(135deg, #05070f 0%, #080b18 50%, #05070f 100%);
        }
        .bg-orb {
            position: fixed; border-radius: 50%; filter: blur(80px);
            animation: orb-drift 20s ease-in-out infinite alternate;
            pointer-events: none;
        }
        .bg-orb-1 {
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(79,142,247,0.12) 0%, transparent 70%);
            top: -200px; right: -200px; animation-duration: 18s;
        }
        .bg-orb-2 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(120,60,220,0.09) 0%, transparent 70%);
            bottom: -150px; left: -150px;
            animation-duration: 24s; animation-direction: alternate-reverse;
        }
        .bg-orb-3 {
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(79,142,247,0.07) 0%, transparent 70%);
            top: 50%; left: 40%; animation-duration: 30s;
        }
        @keyframes orb-drift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(40px, 30px) scale(1.1); }
        }
        .bg-grid {
            position: fixed; inset: 0; z-index: 0; opacity: 0.025;
            background-image:
                linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* Splash Screen */
        #splashScreen {
            position: fixed; inset: 0; z-index: 9999;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            background: var(--bg-deep);
            transition: opacity 0.7s ease, visibility 0.7s ease;
        }
        #splashScreen.hidden {
            opacity: 0; visibility: hidden; pointer-events: none;
        }
        #lottie-container { width: 320px; height: 320px; }

        /* Page Layout */
        .page-wrapper {
            position: relative; z-index: 10;
            width: 100%; height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }

        /* Login Panel */
        .login-panel {
            display: flex;
            width: 100%; max-width: 920px;
            min-height: 560px;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.03),
                0 40px 80px rgba(0,0,0,0.6),
                0 0 60px rgba(79,142,247,0.05);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .login-panel.active { opacity: 1; transform: translateY(0); }

        /* Left Brand Panel */
        .brand-panel {
            flex: 1;
            display: flex; flex-direction: column;
            align-items: flex-start; justify-content: space-between;
            padding: 48px;
            background: linear-gradient(135deg, rgba(79,142,247,0.1) 0%, rgba(100,60,200,0.08) 100%);
            border-right: 1px solid var(--glass-border);
            position: relative; overflow: hidden;
        }
        .brand-panel::before {
            content: '';
            position: absolute; top: -60px; right: -60px;
            width: 280px; height: 280px;
            background: radial-gradient(circle, rgba(79,142,247,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        .brand-logo {
            display: flex; align-items: center; gap: 14px;
            position: relative; z-index: 1;
        }
        .brand-logo-icon {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, var(--accent), #7c5af0);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 900; color: white;
            box-shadow: 0 4px 16px rgba(79,142,247,0.4);
        }
        .brand-logo-text {
            font-size: 20px; font-weight: 800;
            color: var(--text-primary); letter-spacing: -0.5px;
        }
        .brand-logo-text span { color: var(--accent); }
        .brand-hero { position: relative; z-index: 1; }
        .brand-hero-title {
            font-size: 36px; font-weight: 800;
            line-height: 1.15; letter-spacing: -1px;
            color: var(--text-primary); margin-bottom: 16px;
        }
        .brand-hero-title em {
            font-style: normal;
            background: linear-gradient(120deg, var(--accent), #a78bfa);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .brand-hero-subtitle {
            font-size: 14px; color: var(--text-secondary);
            line-height: 1.7; max-width: 280px;
        }
        .brand-features {
            position: relative; z-index: 1;
            display: flex; flex-direction: column; gap: 12px;
        }
        .feature-item {
            display: flex; align-items: center; gap: 10px;
            font-size: 13px; color: var(--text-secondary);
        }
        .feature-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: var(--accent); flex-shrink: 0;
            box-shadow: 0 0 8px rgba(79,142,247,0.6);
        }

        /* Right Form Panel */
        .form-panel {
            width: 400px; flex-shrink: 0;
            background: var(--glass-bg);
            padding: 48px 44px;
            display: flex; flex-direction: column;
            justify-content: center;
        }
        .form-header { margin-bottom: 36px; }
        .form-title {
            font-size: 26px; font-weight: 700;
            color: var(--text-primary); letter-spacing: -0.5px; margin-bottom: 6px;
        }
        .form-subtitle { font-size: 13px; color: var(--text-secondary); }

        /* Alerts */
        .alert {
            padding: 12px 14px; border-radius: 10px;
            font-size: 13px; font-weight: 500; line-height: 1.5;
            margin-bottom: 20px;
            display: flex; align-items: flex-start; gap: 8px;
        }
        .alert-error {
            background: var(--error-bg);
            border: 1px solid rgba(255,77,77,0.2); color: #ff7070;
        }
        .alert-warning {
            background: var(--warning-bg);
            border: 1px solid rgba(245,166,35,0.2); color: #f5c660;
        }
        .alert-icon { font-size: 15px; flex-shrink: 0; margin-top: 1px; }

        /* Form fields */
        .field { margin-bottom: 20px; }
        .field-label {
            display: block; font-size: 12px; font-weight: 600;
            color: var(--text-secondary); margin-bottom: 8px;
            letter-spacing: 0.5px; text-transform: uppercase;
        }
        .field-input-wrap { position: relative; }
        .field-input {
            width: 100%; padding: 13px 16px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 10px;
            color: var(--text-primary); font-family: inherit;
            font-size: 14px; outline: none;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .field-input::placeholder { color: var(--text-muted); }
        .field-input:focus {
            border-color: var(--input-focus-border);
            background: rgba(255,255,255,0.05);
            box-shadow: 0 0 0 3px var(--input-focus-glow);
        }
        .field-input:disabled { opacity: 0.4; cursor: not-allowed; }
        .field-input.has-error { border-color: rgba(255,77,77,0.5); }
        .field-error { display: block; font-size: 11px; color: #ff7070; margin-top: 6px; }

        /* Password toggle */
        .pwd-toggle {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--text-muted); padding: 4px;
            transition: color 0.2s; line-height: 1;
        }
        .pwd-toggle:hover { color: var(--text-secondary); }

        /* Submit button */
        .btn-sign-in {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, var(--accent) 0%, #6a5af5 100%);
            color: white; border: none; border-radius: 10px;
            font-family: inherit; font-size: 15px; font-weight: 600;
            cursor: pointer; margin-top: 8px;
            transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 20px rgba(79,142,247,0.3);
            position: relative; overflow: hidden; letter-spacing: 0.3px;
        }
        .btn-sign-in::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(rgba(255,255,255,0.1), transparent);
            pointer-events: none;
        }
        .btn-sign-in:hover:not(:disabled) {
            opacity: 0.9; transform: translateY(-1px);
            box-shadow: 0 6px 28px rgba(79,142,247,0.45);
        }
        .btn-sign-in:active:not(:disabled) { transform: translateY(0); }
        .btn-sign-in:disabled {
            opacity: 0.35; cursor: not-allowed;
            background: rgba(79,142,247,0.3);
        }

        /* Developer credit */
        .dev-credit {
            margin-top: 28px; text-align: center;
            font-size: 11px; color: var(--text-muted);
        }
        .dev-credit a {
            color: var(--text-secondary); text-decoration: none; font-weight: 500;
            transition: color 0.2s;
        }
        .dev-credit a:hover { color: var(--accent); }

        /* Responsive */
        @media (max-width: 700px) {
            .brand-panel { display: none; }
            .form-panel { width: 100%; padding: 36px 28px; }
            .login-panel { min-height: unset; max-width: 420px; }
        }
    </style>
</head>
<body>

    <!-- Animated Background -->
    <div class="bg-canvas"></div>
    <div class="bg-grid"></div>
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>
    <div class="bg-orb bg-orb-3"></div>

    <!-- Splash Screen -->
    <div id="splashScreen">
        <div id="lottie-container"></div>
    </div>

    <!-- Main Page -->
    <div class="page-wrapper">
        <div class="login-panel" id="loginPanel">

            <!-- Left Brand Panel -->
            <div class="brand-panel">
                <div class="brand-logo">
                    <div class="brand-logo-icon">C</div>
                    <div class="brand-logo-text">CURTISS <span>ERP</span></div>
                </div>

                <div class="brand-hero">
                    <div class="brand-hero-title">
                        Built for<br><em>modern</em><br>business.
                    </div>
                    <div class="brand-hero-subtitle">
                        A complete enterprise platform to manage sales, inventory, finance, and your field team &mdash; all in one place.
                    </div>
                </div>

                <div class="brand-features">
                    <div class="feature-item"><div class="feature-dot"></div>Real-time inventory &amp; stock control</div>
                    <div class="feature-item"><div class="feature-dot"></div>Double-entry accounting &amp; reporting</div>
                    <div class="feature-item"><div class="feature-dot"></div>Rep tracking &amp; route performance</div>
                    <div class="feature-item"><div class="feature-dot"></div>Automated collection &amp; payment flows</div>
                </div>
            </div>

            <!-- Right Form Panel -->
            <div class="form-panel">
                <div class="form-header">
                    <div class="form-title">Welcome back</div>
                    <div class="form-subtitle">Sign in to your workspace to continue</div>
                </div>

                <?php if(!empty($data['lockout_err'])): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">&#9888;</span>
                    <span><?= $data['lockout_err'] ?></span>
                </div>
                <?php endif; ?>

                <?php if(!empty($data['csrf_err'])): ?>
                <div class="alert alert-warning">
                    <span class="alert-icon">&#9889;</span>
                    <span><?= $data['csrf_err'] ?></span>
                </div>
                <?php endif; ?>

                <?php if(!empty($data['system_err'])): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">&#10005;</span>
                    <span><strong>System Error:</strong> <?= htmlspecialchars($data['system_err']) ?></span>
                </div>
                <?php endif; ?>

                <form action="<?= APP_URL ?>/auth/login" method="POST" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="field">
                        <label class="field-label" for="username">Username</label>
                        <div class="field-input-wrap">
                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="field-input <?= !empty($data['username_err']) ? 'has-error' : '' ?>"
                                value="<?= isset($data['username']) ? htmlspecialchars($data['username']) : '' ?>"
                                placeholder="Enter your username"
                                autocomplete="username"
                                autofocus
                                <?= !empty($data['lockout_err']) ? 'disabled' : '' ?>
                            >
                        </div>
                        <?php if(!empty($data['username_err'])): ?>
                        <span class="field-error"><?= $data['username_err'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="field-label" for="password">Password</label>
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
                            <button type="button" class="pwd-toggle" onclick="togglePwd()" aria-label="Toggle password visibility">
                                <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <?php if(!empty($data['password_err'])): ?>
                        <span class="field-error"><?= $data['password_err'] ?></span>
                        <?php endif; ?>
                    </div>

                    <button
                        type="submit"
                        class="btn-sign-in"
                        id="submitBtn"
                        <?= !empty($data['lockout_err']) ? 'disabled' : '' ?>
                    >Sign In</button>
                </form>

                <div class="dev-credit">
                    Powered by <a href="https://suzxlabs.com" target="_blank" rel="noopener noreferrer">suzxlabs</a>
                </div>
            </div>

        </div>
    </div>

    <script>
        const hasErrors = <?= (!empty($data['lockout_err']) || !empty($data['csrf_err']) || !empty($data['system_err']) || !empty($data['username_err']) || !empty($data['password_err'])) ? 'true' : 'false' ?>;
        const splashScreen = document.getElementById('splashScreen');
        const loginPanel   = document.getElementById('loginPanel');

        function revealLogin() {
            splashScreen.classList.add('hidden');
            setTimeout(function() { loginPanel.classList.add('active'); }, 300);
        }

        if (hasErrors) {
            splashScreen.style.display = 'none';
            loginPanel.classList.add('active');
        } else {
            lottie.loadAnimation({
                container: document.getElementById('lottie-container'),
                renderer: 'svg',
                loop: true,
                autoplay: true,
                path: '<?= APP_URL ?>/assets/lottie/scene.json'
            });
            // After 3 seconds go directly to login
            setTimeout(revealLogin, 3000);
        }

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

        document.getElementById('loginForm').addEventListener('submit', function() {
            var btn = document.getElementById('submitBtn');
            btn.textContent = 'Signing in\u2026';
            btn.disabled = true;
        });

        document.addEventListener('DOMContentLoaded', function() {
            var redirectUrl = sessionStorage.getItem('redirect_url');
            if (redirectUrl) {
                var form  = document.getElementById('loginForm');
                var input = form.querySelector('input[name="redirect_url"]');
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'redirect_url';
                    form.appendChild(input);
                }
                input.value = redirectUrl;
            }
        });
    </script>

    <?php if(isset($data['debug_console'])): ?>
    <script>
        console.error("ERP DEBUG LOG: <?= $data['debug_console'] ?>");
    </script>
    <?php endif; ?>

</body>
</html>