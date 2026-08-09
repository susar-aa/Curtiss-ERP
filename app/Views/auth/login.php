<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Login</title>
    <style>
        :root {
            --bg-color: #f0f2f5;
            --card-bg: rgba(255, 255, 255, 0.85);
            --text-main: #333;
            --border-color: rgba(0, 0, 0, 0.1);
            --primary-blue: #0066cc;
            --splash-bg: #ffffff;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg-color: #1a1a2e;
                --card-bg: rgba(30, 30, 45, 0.85);
                --text-main: #e0e0e0;
                --border-color: rgba(255, 255, 255, 0.1);
                --primary-blue: #0a84ff;
                --splash-bg: #111118;
            }
        }

        body {
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--bg-color);
            background-image: radial-gradient(circle at top right, rgba(0,102,204,0.1), transparent 40%),
                              radial-gradient(circle at bottom left, rgba(0,102,204,0.1), transparent 40%);
            color: var(--text-main);
        }

        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 360px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.5s ease;
            position: absolute;
            z-index: 10;
        }

        .login-card.active {
            opacity: 1;
            pointer-events: all;
            position: relative;
        }

        .splash-screen {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: var(--splash-bg);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: opacity 0.8s ease, visibility 0.8s;
        }

        .splash-screen.hidden {
            opacity: 0;
            visibility: hidden;
        }

        #lottie-container {
            width: 300px;
            height: 300px;
            transition: opacity 0.5s ease;
        }

        .welcome-content {
            opacity: 0;
            position: absolute;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: opacity 0.8s ease;
            pointer-events: none;
        }
        
        .welcome-content.active {
            opacity: 1;
            pointer-events: all;
        }

        .welcome-title {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
            color: var(--text-main);
        }

        .welcome-subtitle {
            font-size: 16px;
            color: #888;
            margin-bottom: 30px;
        }
        
        .btn-large {
            padding: 14px 32px;
            background: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            box-shadow: 0 4px 15px rgba(0, 102, 204, 0.3);
        }

        .btn-large:hover {
            background: #005bb5;
            transform: translateY(-2px);
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .subtitle {
            font-size: 14px;
            color: #888;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: transparent;
            color: var(--text-main);
            font-size: 15px;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: #005bb5;
        }

        .error-text {
            color: #ff3b30;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js"></script>
</head>
<body>

    <!-- Splash Screen -->
    <div class="splash-screen" id="splashScreen">
        <div id="lottie-container"></div>
        
        <div class="welcome-content" id="welcomeContent">
            <div class="welcome-title">Welcome to Curtiss ERP</div>
            <div class="welcome-subtitle">Enterprise Business Engine</div>
            <button class="btn-large" onclick="showLogin()">Go to Login</button>
        </div>
    </div>

    <div class="login-card">
        <div class="logo"> CURTISS ERP</div>
        <div class="subtitle">Enterprise Business Engine</div>

        <!-- Lockout and CSRF Alerts -->
        <?php if(!empty($data['lockout_err'])): ?>
            <div style="background: rgba(255, 59, 48, 0.15); border: 1px dashed #ff3b30; color: #ff453a; padding: 12px; border-radius: 8px; font-size: 13px; font-weight: 500; margin-bottom: 20px; line-height: 1.4; text-align: left;">
                <i class="ph ph-shield-warning" style="vertical-align: middle; margin-right: 4px;"></i> <?= $data['lockout_err'] ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($data['csrf_err'])): ?>
            <div style="background: rgba(255, 149, 0, 0.15); border: 1px dashed #ff9500; color: #ffb340; padding: 12px; border-radius: 8px; font-size: 13px; font-weight: 500; margin-bottom: 20px; line-height: 1.4; text-align: left;">
                <i class="ph ph-warning-circle" style="vertical-align: middle; margin-right: 4px;"></i> <?= $data['csrf_err'] ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($data['system_err'])): ?>
            <div style="background: rgba(255, 59, 48, 0.15); border: 1px dashed #ff3b30; color: #ff453a; padding: 12px; border-radius: 8px; font-size: 13px; font-weight: 500; margin-bottom: 20px; line-height: 1.4; text-align: left; word-break: break-all;">
                <strong>System Error:</strong> <?= htmlspecialchars($data['system_err']) ?>
            </div>
        <?php endif; ?>

        <form action="<?= APP_URL ?>/auth/login" method="POST">
            <!-- Hidden CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" class="form-control" value="<?= isset($data['username']) ? htmlspecialchars($data['username']) : '' ?>" autofocus <?= !empty($data['lockout_err']) ? 'disabled' : '' ?>>
                <span class="error-text"><?= isset($data['username_err']) ? $data['username_err'] : '' ?></span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" class="form-control" <?= !empty($data['lockout_err']) ? 'disabled' : '' ?>>
                <span class="error-text"><?= isset($data['password_err']) ? $data['password_err'] : '' ?></span>
            </div>

            <button type="submit" class="btn-submit" <?= !empty($data['lockout_err']) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>Sign In</button>
        </form>
    </div>

    <script>
        // Check if there is an error message, if so, skip splash screen directly
        const hasErrors = <?= (!empty($data['lockout_err']) || !empty($data['csrf_err']) || !empty($data['system_err']) || !empty($data['username_err']) || !empty($data['password_err'])) ? 'true' : 'false' ?>;
        
        const splashScreen = document.getElementById('splashScreen');
        const loginCard = document.querySelector('.login-card');
        const lottieContainer = document.getElementById('lottie-container');
        const welcomeContent = document.getElementById('welcomeContent');

        if (hasErrors) {
            splashScreen.style.display = 'none';
            loginCard.classList.add('active');
        } else {
            // Load Lottie animation
            const anim = lottie.loadAnimation({
                container: lottieContainer,
                renderer: 'svg',
                loop: true,
                autoplay: true,
                path: '<?= APP_URL ?>/assets/lottie/scene.json' // path to Lottie JSON
            });

            // After 3 seconds, hide animation and show welcome
            setTimeout(() => {
                lottieContainer.style.opacity = '0';
                setTimeout(() => {
                    lottieContainer.style.display = 'none';
                    welcomeContent.classList.add('active');
                }, 500); // Wait for fade out
            }, 3000);
        }

        function showLogin() {
            splashScreen.classList.add('hidden');
            setTimeout(() => {
                loginCard.classList.add('active');
            }, 400); // Show login card halfway through splash fade
        }
    </script>
    <?php if(isset($data['debug_console'])): ?>
    <script>
        console.error("ERP DEBUG LOG: <?= $data['debug_console'] ?>");
        console.warn("System self-healing engaged: If you tried 'admin123', the database hash has been repaired. Please click Sign In one more time.");
    </script>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const redirectUrl = sessionStorage.getItem('redirect_url');
            if (redirectUrl) {
                const form = document.querySelector('form');
                if (form) {
                    let input = form.querySelector('input[name="redirect_url"]');
                    if (!input) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'redirect_url';
                        form.appendChild(input);
                    }
                    input.value = redirectUrl;
                }
            }
        });
    </script>
</body>
</html>