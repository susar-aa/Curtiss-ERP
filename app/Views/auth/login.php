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
            --bg-color: #f8fafc;
            --accent: #2563eb;
            --accent-hover: #1d4ed8;
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --error-bg: #fef2f2;
            --error-text: #ef4444;
            --warning-bg: #fffbeb;
            --warning-text: #f59e0b;
            --input-bg: #f8fafc;
            --input-border: #cbd5e1;
            --input-focus: #3b82f6;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -4px rgba(0,0,0,0.05);
        }

        html, body {
            width: 100%; min-height: 100vh;
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--bg-color);
            color: var(--text-primary);
            overflow-x: hidden;
            display: flex; flex-direction: column;
        }

        /* Animated Background Pattern */
        .bg-pattern {
            position: fixed; inset: 0; z-index: 0; opacity: 0.4;
            background-image: radial-gradient(var(--input-border) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
        }
        
        .bg-gradient {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background: radial-gradient(circle at 15% 50%, rgba(37, 99, 235, 0.05), transparent 40%),
                        radial-gradient(circle at 85% 30%, rgba(59, 130, 246, 0.08), transparent 40%);
        }

        /* Splash Screen */
        #splashScreen {
            position: fixed; inset: 0; z-index: 9999;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            background: #ffffff;
            transition: opacity 0.6s ease, visibility 0.6s ease;
        }
        #splashScreen.hidden {
            opacity: 0; visibility: hidden; pointer-events: none;
        }
        #lottie-container { width: 320px; height: 320px; }

        /* Main Container */
        .app-container {
            position: relative; z-index: 10;
            width: 100%; max-width: 1000px;
            margin: 0 auto; padding: 40px 20px;
            flex-grow: 1; display: flex; flex-direction: column;
        }
        
        .header {
            text-align: center; margin-bottom: 40px;
            opacity: 0; transform: translateY(-20px);
            transition: opacity 0.6s ease 0.2s, transform 0.6s ease 0.2s;
        }
        .header.active { opacity: 1; transform: translateY(0); }
        .logo {
            font-size: 24px; font-weight: 800; letter-spacing: -0.5px;
            color: var(--text-primary); margin-bottom: 8px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .logo-icon {
            width: 36px; height: 36px;
            background: var(--accent); color: white;
            border-radius: 10px; display: flex; align-items: center; justify-content: center;
            font-size: 18px; box-shadow: var(--shadow-sm);
        }
        .title-text {
            font-size: 32px; font-weight: 700; letter-spacing: -0.5px;
            color: var(--text-primary);
        }
        .subtitle-text {
            font-size: 16px; color: var(--text-secondary);
        }

        /* Screen Transitions */
        .screen {
            display: none; opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.4s ease, transform 0.4s ease;
        }
        .screen.active {
            display: block; opacity: 1; transform: translateY(0);
        }
        
        /* Alerts */
        .alert-container { max-width: 400px; margin: 0 auto 20px auto; }
        .alert {
            padding: 12px 16px; border-radius: 12px;
            font-size: 13px; font-weight: 500; line-height: 1.5;
            display: flex; align-items: flex-start; gap: 10px;
            margin-bottom: 12px;
        }
        .alert-error { background: var(--error-bg); color: var(--error-text); border: 1px solid rgba(239, 68, 68, 0.2); }
        .alert-warning { background: var(--warning-bg); color: var(--warning-text); border: 1px solid rgba(245, 158, 11, 0.2); }
        .alert-icon { font-size: 16px; margin-top: 1px; }

        /* User Grid */
        .user-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px; max-width: 900px; margin: 0 auto;
        }
        
        .user-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px; padding: 24px;
            display: flex; flex-direction: column; align-items: center;
            text-align: center; cursor: pointer;
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
        }
        .user-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--accent);
        }
        
        .user-avatar {
            width: 72px; height: 72px; border-radius: 50%;
            background: #f1f5f9; display: flex; align-items: center; justify-content: center;
            font-size: 28px; font-weight: 600; color: var(--accent);
            margin-bottom: 16px; border: 2px solid white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.06);
        }
        .user-name {
            font-size: 18px; font-weight: 700; color: var(--text-primary);
            margin-bottom: 4px;
        }
        .user-role {
            font-size: 13px; font-weight: 500; color: var(--text-secondary);
            background: #f1f5f9; padding: 4px 12px; border-radius: 20px;
        }

        /* Password Form Screen */
        .auth-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px; padding: 40px;
            width: 100%; max-width: 400px;
            margin: 0 auto; box-shadow: var(--shadow-lg);
            text-align: center;
        }
        
        .selected-user-info {
            display: flex; flex-direction: column; align-items: center;
            margin-bottom: 32px; padding-bottom: 24px;
            border-bottom: 1px solid var(--card-border);
        }
        .selected-avatar {
            width: 64px; height: 64px; border-radius: 50%;
            background: #f1f5f9; display: flex; align-items: center; justify-content: center;
            font-size: 24px; font-weight: 600; color: var(--accent);
            margin-bottom: 12px; box-shadow: var(--shadow-sm);
        }
        
        .field { margin-bottom: 20px; text-align: left; }
        .field-label {
            display: block; font-size: 13px; font-weight: 600;
            color: var(--text-primary); margin-bottom: 8px;
        }
        .field-input-wrap { position: relative; }
        .field-input {
            width: 100%; padding: 14px 16px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 12px;
            color: var(--text-primary); font-family: inherit;
            font-size: 15px; outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .field-input:focus {
            border-color: var(--input-focus);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            background: #ffffff;
        }
        .field-input.has-error { border-color: var(--error-text); }
        .field-error { display: block; font-size: 12px; color: var(--error-text); margin-top: 6px; }
        
        .pwd-toggle {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--text-muted); padding: 4px;
            transition: color 0.2s; display: flex; align-items: center;
        }
        .pwd-toggle:hover { color: var(--text-secondary); }

        .btn-submit {
            width: 100%; padding: 14px;
            background: var(--accent); color: white;
            border: none; border-radius: 12px;
            font-family: inherit; font-size: 15px; font-weight: 600;
            cursor: pointer; margin-top: 8px;
            transition: background 0.2s, transform 0.1s;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }
        .btn-submit:hover:not(:disabled) { background: var(--accent-hover); transform: translateY(-1px); }
        .btn-submit:active:not(:disabled) { transform: translateY(0); }
        .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }

        .btn-back {
            background: none; border: none;
            color: var(--text-secondary); font-size: 14px; font-weight: 500;
            cursor: pointer; margin-top: 20px; display: inline-flex; align-items: center; gap: 6px;
            transition: color 0.2s;
        }
        .btn-back:hover { color: var(--text-primary); }

        /* Developer credit */
        .dev-credit {
            margin-top: auto; padding: 20px; text-align: center;
            font-size: 12px; color: var(--text-muted);
        }
        .dev-credit a {
            color: var(--text-secondary); text-decoration: none; font-weight: 600;
            transition: color 0.2s;
        }
        .dev-credit a:hover { color: var(--accent); }
    </style>
</head>
<body>

    <!-- Backgrounds -->
    <div class="bg-gradient"></div>
    <div class="bg-pattern"></div>

    <!-- Splash Screen -->
    <div id="splashScreen">
        <div id="lottie-container"></div>
    </div>

    <div class="app-container">
        
        <!-- Header -->
        <div class="header" id="pageHeader">
            <div class="logo">
                <div class="logo-icon">C</div>
                CURTISS ERP
            </div>
            <div class="title-text" id="headerTitle">Who are you?</div>
            <div class="subtitle-text" id="headerSubtitle">Select your account to sign in</div>
        </div>

        <!-- Global Alerts -->
        <div class="alert-container" id="alertContainer">
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
        </div>

        <!-- SCREEN 1: User Selection -->
        <div class="screen" id="userSelectionScreen">
            <div class="user-grid">
                <?php 
                $eligible_users = $data['eligible_users'] ?? [];
                if (empty($eligible_users)):
                ?>
                    <div style="grid-column: 1/-1; text-align: center; color: var(--text-secondary); padding: 40px;">
                        No eligible users found. Please contact an administrator.
                    </div>
                <?php else: ?>
                    <?php foreach ($eligible_users as $user): ?>
                    <div class="user-card" onclick="selectUser('<?= htmlspecialchars($user->username) ?>', '<?= htmlspecialchars($user->full_name) ?>', '<?= htmlspecialchars($user->role) ?>')">
                        <div class="user-avatar">
                            <?= strtoupper(substr($user->full_name, 0, 1)) ?>
                        </div>
                        <div class="user-name"><?= htmlspecialchars($user->full_name) ?></div>
                        <div class="user-role"><?= htmlspecialchars($user->role) ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- SCREEN 2: Password Form -->
        <div class="screen" id="passwordScreen">
            <div class="auth-card">
                <div class="selected-user-info">
                    <div class="selected-avatar" id="selectedAvatar">U</div>
                    <div class="user-name" id="selectedUsernameDisplay">User Name</div>
                    <div class="user-role" id="selectedUserRole">Role</div>
                </div>

                <form action="<?= APP_URL ?>/auth/login" method="POST" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="username" id="formUsernameInput" value="<?= isset($data['username']) ? htmlspecialchars($data['username']) : '' ?>">

                    <?php if(!empty($data['username_err'])): ?>
                    <div class="alert alert-error" style="text-align: left;">
                        <span><?= $data['username_err'] ?></span>
                    </div>
                    <?php endif; ?>

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
                        Authorize & Sign In
                    </button>
                </form>

                <button class="btn-back" onclick="goBack()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Not you? Go back
                </button>
            </div>
        </div>
        
        <div class="dev-credit">
            Powered by <a href="https://suzxlabs.com" target="_blank" rel="noopener noreferrer">suzxlabs</a>
        </div>
    </div>

    <script>
        const hasErrors = <?= (!empty($data['lockout_err']) || !empty($data['csrf_err']) || !empty($data['system_err']) || !empty($data['username_err']) || !empty($data['password_err'])) ? 'true' : 'false' ?>;
        const previouslySelectedUsername = <?= json_encode($data['username'] ?? '') ?>;
        
        const splashScreen = document.getElementById('splashScreen');
        const header = document.getElementById('pageHeader');
        const headerTitle = document.getElementById('headerTitle');
        const headerSubtitle = document.getElementById('headerSubtitle');
        
        const screenSelection = document.getElementById('userSelectionScreen');
        const screenPassword = document.getElementById('passwordScreen');
        
        const formUsernameInput = document.getElementById('formUsernameInput');
        const selectedAvatar = document.getElementById('selectedAvatar');
        const selectedUsernameDisplay = document.getElementById('selectedUsernameDisplay');
        const selectedUserRole = document.getElementById('selectedUserRole');
        const passwordInput = document.getElementById('password');

        // Fetch users array for restoring state
        const eligibleUsers = <?= json_encode($data['eligible_users'] ?? []) ?>;

        function showScreen(screenId) {
            document.querySelectorAll('.screen').forEach(s => {
                s.classList.remove('active');
                setTimeout(() => { if(!s.classList.contains('active')) s.style.display = 'none'; }, 400);
            });
            
            const target = document.getElementById(screenId);
            target.style.display = 'block';
            setTimeout(() => target.classList.add('active'), 50);

            if (screenId === 'userSelectionScreen') {
                headerTitle.textContent = "Who are you?";
                headerSubtitle.textContent = "Select your account to sign in";
            } else {
                headerTitle.textContent = "Welcome back";
                headerSubtitle.textContent = "Please authorize to continue";
            }
        }

        function selectUser(username, fullName, role) {
            formUsernameInput.value = username;
            selectedUsernameDisplay.textContent = fullName;
            selectedUserRole.textContent = role;
            selectedAvatar.textContent = fullName.charAt(0).toUpperCase();
            
            // Clear any previous alerts when switching users
            document.getElementById('alertContainer').style.display = 'none';
            
            showScreen('passwordScreen');
            setTimeout(() => passwordInput.focus(), 400);
        }

        function goBack() {
            formUsernameInput.value = "";
            passwordInput.value = "";
            showScreen('userSelectionScreen');
        }

        function initApp() {
            header.classList.add('active');
            
            if (hasErrors && previouslySelectedUsername) {
                // Find role of previously selected user
                let role = "User";
                let fullName = previouslySelectedUsername;
                const userObj = eligibleUsers.find(u => u.username === previouslySelectedUsername);
                if (userObj) {
                    role = userObj.role;
                    fullName = userObj.full_name;
                }
                
                selectUser(previouslySelectedUsername, fullName, role);
                document.getElementById('alertContainer').style.display = 'block'; // Show errors
            } else {
                showScreen('userSelectionScreen');
            }
        }

        if (hasErrors) {
            splashScreen.style.display = 'none';
            initApp();
        } else {
            lottie.loadAnimation({
                container: document.getElementById('lottie-container'),
                renderer: 'svg',
                loop: true,
                autoplay: true,
                path: '<?= APP_URL ?>/assets/lottie/scene.json'
            });
            setTimeout(() => {
                splashScreen.classList.add('hidden');
                setTimeout(initApp, 400);
            }, 3000);
        }

        function togglePwd() {
            var pwd = document.getElementById('password');
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
            btn.textContent = 'Authorizing...';
            btn.disabled = true;
        });

        document.addEventListener('DOMContentLoaded', function() {
            var redirectUrl = sessionStorage.getItem('redirect_url');
            if (redirectUrl) {
                var form = document.getElementById('loginForm');
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