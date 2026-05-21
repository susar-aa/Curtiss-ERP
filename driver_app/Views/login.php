<div class="card" style="margin: 40px auto 20px; max-width: 380px; padding: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid var(--border); background: var(--surface);">
    <div style="text-align: center; margin-bottom: 25px;">
        <span style="font-size: 50px;">🚚</span>
        <h2 style="margin: 15px 0 5px; font-size: 24px; font-weight: 800; color: var(--text-dark);">Driver Portal</h2>
        <p style="color: var(--text-muted); font-size: 14px; margin: 0;">Sign in to access your daily routes</p>
    </div>

    <form action="<?= APP_URL ?>/driver/auth/login" method="POST" style="display: flex; flex-direction: column; gap: 5px;">
        <label class="form-label" style="font-weight: 700;">Username</label>
        <input type="text" name="username" required autofocus class="form-input" placeholder="Enter your username" style="margin-bottom: 15px;">

        <label class="form-label" style="font-weight: 700;">Password</label>
        <input type="password" name="password" required class="form-input" placeholder="••••••••" style="margin-bottom: 20px;">

        <button type="submit" class="btn-primary" style="background: var(--primary); padding: 15px; font-size: 16px;">
            Secure Log In
        </button>
    </form>
</div>
