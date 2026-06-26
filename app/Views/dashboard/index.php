<?php /* Premium Modernized Dashboard */ ?>
<style>
/* ── BACKGROUND ── */
#dashBg {
    position: fixed; inset: 0; z-index: -2;
    background: #0f0f1a;
    background-size: cover; background-position: center;
    transition: opacity 1.2s ease; opacity: 0;
}
#dashBg::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(150deg, rgba(8,8,22,.6) 0%, rgba(8,8,22,.35) 55%, rgba(8,8,22,.55) 100%);
}
#dashBg.ready { opacity: 1; }

/* ── CARD BASE ── */
.d-card {
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(28px);
    -webkit-backdrop-filter: blur(28px);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    overflow: hidden;
    transition: transform .25s, box-shadow .25s, border-color .25s;
}
.d-card:hover { 
    transform: translateY(-2px); 
    box-shadow: 0 12px 40px rgba(0,0,0,0.28); 
    border-color: rgba(255,255,255,0.18);
}
@media (prefers-color-scheme: light) {
    .d-card {
        background: rgba(255,255,255,0.7);
        border-color: rgba(0,0,0,0.08);
        box-shadow: 0 8px 32px rgba(0,0,0,0.06);
    }
    .d-card:hover {
        border-color: rgba(79,70,229,0.2);
        box-shadow: 0 12px 40px rgba(79,70,229,0.08);
    }
}

/* ── TOP BAR (Search Area) ── */
.db-topbar {
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 24px; flex-wrap: wrap;
}
.db-search {
    display: flex; align-items: center; gap: 10px;
    flex: 1; min-width: 280px; max-width: 520px; height: 46px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 23px; padding: 0 18px;
    backdrop-filter: blur(20px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    cursor: text; transition: background .2s, box-shadow .2s, border-color .2s;
}
@media (prefers-color-scheme: light) {
    .db-search {
        background: rgba(255,255,255,0.8);
        border-color: rgba(0,0,0,0.1);
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }
}
.db-search:hover, .db-search:focus-within {
    background: rgba(255,255,255,0.15);
    border-color: rgba(79,70,229,0.4);
    box-shadow: 0 6px 30px rgba(79,70,229,.2);
}
@media (prefers-color-scheme: light) {
    .db-search:hover, .db-search:focus-within {
        background: #fff;
        border-color: rgba(79,70,229,0.4);
    }
}
.db-search i { color: var(--text-muted); font-size: 16px; }
.db-search input {
    border: none; background: transparent; outline: none;
    font-size: 14px; color: var(--text-main); width: 100%; font-family: inherit;
}
.db-search input::placeholder { color: var(--text-muted); }

.db-actions { display: flex; align-items: center; gap: 8px; margin-left: auto; }
.db-icon-btn {
    width: 44px; height: 44px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: var(--text-main); opacity: 0.85; font-size: 18px; text-decoration: none;
    background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15);
    backdrop-filter: blur(16px);
    box-shadow: 0 4px 16px rgba(0,0,0,.1);
    transition: all .18s; position: relative; cursor: pointer;
}
@media (prefers-color-scheme: light) {
    .db-icon-btn {
        background: rgba(255,255,255,0.8);
        border-color: rgba(0,0,0,0.08);
        box-shadow: 0 4px 16px rgba(0,0,0,0.03);
    }
}
.db-icon-btn:hover { background: rgba(79,70,229,0.15); border-color: rgba(79,70,229,0.3); opacity: 1; color: var(--text-accent); }
.db-nbadge {
    background: #ef4444; color: #fff; border-radius: 10px;
    padding: 1px 5px; font-size: 9px; font-weight: 700;
    position: absolute; top: 2px; right: 2px;
    border: 2px solid rgba(0,0,0,.15);
}

/* ── GRID SYSTEM ── */
.db-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 24px;
}
@media (max-width: 1024px) {
    .db-grid { grid-template-columns: 1fr; }
}

/* ── LEFT COLUMN ── */
.db-col-left {
    display: flex; flex-direction: column; gap: 24px;
}

/* ── QUICK ACCESS GRID ── */
.d-quick { padding: 24px; }
.d-quick-header { 
    font-size: 14px; font-weight: 700; color: var(--text-main); 
    margin-bottom: 20px; letter-spacing: .5px; text-transform: uppercase;
    opacity: 0.8;
}
.d-quick-grid { 
    display: grid; 
    grid-template-columns: repeat(3, 1fr); 
    gap: 16px; 
}
@media (max-width: 600px) {
    .d-quick-grid { grid-template-columns: repeat(2, 1fr); }
}
.d-qbtn {
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px;
    padding: 24px 16px; border-radius: 16px;
    text-decoration: none; color: var(--text-main);
    background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08);
    transition: all .2s ease;
    text-align: center;
}
@media (prefers-color-scheme: light) {
    .d-qbtn {
        background: rgba(0,0,0,0.02);
        border-color: rgba(0,0,0,0.05);
    }
}
.d-qbtn:hover {
    background: rgba(79,70,229,.15); border-color: rgba(79,70,229,.3);
    color: var(--text-accent); transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(79,70,229,0.12);
}
.d-qbtn i { font-size: 28px; transition: transform 0.2s ease; }
.d-qbtn:hover i { transform: scale(1.1); }
.d-qbtn span { font-size: 13px; font-weight: 600; line-height: 1.3; }

/* ── RIGHT COLUMN ── */
.db-col-right {
    display: flex; flex-direction: column; gap: 24px;
}

/* ── REDESIGNED PROFILE CARD ── */
.d-profile {
    padding: 30px 24px 24px;
    display: flex; flex-direction: column; align-items: center; text-align: center; gap: 12px;
}
.d-profile-avatar {
    width: 80px; height: 80px; border-radius: 50%;
    background: linear-gradient(135deg,#667eea,#764ba2);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 30px; font-weight: 700;
    box-shadow: 0 6px 20px rgba(102,126,234,.4);
    margin-bottom: 4px;
}
.d-profile-name { font-size: 18px; font-weight: 700; color: var(--text-main); }
.d-profile-role {
    font-size: 11px; color: var(--text-accent); font-weight: 700;
    background: rgba(79,70,229,0.12); border-radius: 20px;
    padding: 4px 14px; display: inline-block;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.d-profile-greeting { font-size: 13px; color: var(--text-muted); margin-top: 4px; }
.d-profile-divider { width: 100%; height: 1px; background: rgba(255,255,255,.1); margin: 6px 0; }
@media (prefers-color-scheme: light) {
    .d-profile-divider { background: rgba(0,0,0,0.06); }
}
.d-profile-stat {
    display: flex; justify-content: space-between; width: 100%;
    font-size: 12px; color: var(--text-muted);
}
.d-profile-stat strong { color: var(--text-main); font-weight: 600; }
.d-profile-logout-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; margin-top: 14px; padding: 10px; border-radius: 12px;
    background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.25);
    color: rgba(239,68,68,.9); text-decoration: none;
    font-size: 13px; font-weight: 600; transition: all .18s;
}
.d-profile-logout-btn:hover {
    background: rgba(239,68,68,.22);
    box-shadow: 0 4px 12px rgba(239,68,68,0.15);
}

/* ── DATE / TIME CARD ── */
.d-datetime {
    padding: 24px;
    display: flex; flex-direction: column; justify-content: center; gap: 4px;
}
.d-time { font-size: 40px; font-weight: 300; color: var(--text-main); letter-spacing: -1.5px; line-height: 1; }
.d-time span { font-size: 16px; font-weight: 400; opacity: .6; }
.d-date { font-size: 15px; color: var(--text-main); font-weight: 500; margin-top: 6px; }
.d-day  { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: .8px; font-weight: 600; }

/* ── NOTIFICATION CARD ── */
.d-notif { padding: 24px; }
.d-notif-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px;
}
.d-notif-title { font-size: 13px; font-weight: 700; color: var(--text-main); text-transform: uppercase; opacity: 0.8; }
.d-notif-count {
    background: #ef4444; color: #fff; border-radius: 12px;
    padding: 2px 8px; font-size: 10px; font-weight: 700;
}
.d-notif-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,.07);
}
@media (prefers-color-scheme: light) {
    .d-notif-item { border-bottom-color: rgba(0,0,0,0.05); }
}
.d-notif-item:last-child { border-bottom: none; }
.d-notif-dot { width: 8px; height: 8px; border-radius: 50%; background: #667eea; flex-shrink: 0; }
.d-notif-text { font-size: 12px; color: var(--text-muted); line-height: 1.4; }
.d-notif-empty {
    text-align: center; padding: 24px 0;
    color: var(--text-muted); font-size: 13px;
}
.d-notif-empty i { font-size: 28px; display: block; margin-bottom: 8px; opacity: 0.6; }
.d-notif-link {
    display: block; text-align: center; margin-top: 14px;
    font-size: 12px; color: var(--text-accent); text-decoration: none;
    padding: 8px; border-radius: 10px;
    background: rgba(79,70,229,.1); font-weight: 600; transition: background .18s;
}
.d-notif-link:hover { background: rgba(79,70,229,.18); }
</style>

<!-- Background Layer -->
<div id="dashBg"></div>

<!-- Top Bar -->
<div class="db-topbar">
    <div class="db-search">
        <i class="ph ph-magnifying-glass"></i>
        <input type="text" id="dashSearch" placeholder="Search customers, invoices, products or modules..." autocomplete="off">
    </div>
    <div class="db-actions">
        <?php if (!empty($storeUrl)): ?>
        <a href="<?= htmlspecialchars($storeUrl) ?>" target="_blank" class="db-icon-btn" title="Store">
            <i class="ph ph-storefront"></i>
        </a>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/notification" class="db-icon-btn" title="Notifications">
            <i class="ph ph-bell"></i>
            <?php if ($notifCount > 0): ?>
                <span class="db-nbadge"><?= $notifCount ?></span>
            <?php endif; ?>
        </a>
    </div>
</div>

<!-- Main Grid Layout -->
<div class="db-grid">

    <!-- LEFT COLUMN: Quick Access -->
    <div class="db-col-left">
        <div class="d-card d-quick">
            <div class="d-quick-header">Quick Access</div>
            <div class="d-quick-grid">
                <a href="<?= APP_URL ?>/sales/create" class="d-qbtn">
                    <i class="ph ph-file-plus" style="color: #3b82f6;"></i>
                    <span>Create Invoice</span>
                </a>
                <a href="<?= APP_URL ?>/salesorder/create" class="d-qbtn">
                    <i class="ph ph-file-text" style="color: #10b981;"></i>
                    <span>Create Sales Order</span>
                </a>
                <a href="<?= APP_URL ?>/customer" class="d-qbtn">
                    <i class="ph ph-users" style="color: #6366f1;"></i>
                    <span>Customers</span>
                </a>
                <a href="<?= APP_URL ?>/supplier" class="d-qbtn">
                    <i class="ph ph-factory" style="color: #f59e0b;"></i>
                    <span>Suppliers</span>
                </a>
                <a href="<?= APP_URL ?>/inventory" class="d-qbtn">
                    <i class="ph ph-package" style="color: #ec4899;"></i>
                    <span>Inventory</span>
                </a>
                <a href="<?= APP_URL ?>/reptracking" class="d-qbtn">
                    <i class="ph ph-map-pin" style="color: #06b6d4;"></i>
                    <span>Route Control</span>
                </a>
                <a href="<?= APP_URL ?>/customerpayment" class="d-qbtn">
                    <i class="ph ph-hand-coins" style="color: #14b8a6;"></i>
                    <span>Payments</span>
                </a>
                <a href="<?= APP_URL ?>/grn" class="d-qbtn">
                    <i class="ph ph-tray-arrow-down" style="color: #8b5cf6;"></i>
                    <span>GRN</span>
                </a>
                <a href="<?= APP_URL ?>/purchase" class="d-qbtn">
                    <i class="ph ph-shopping-cart" style="color: #ef4444;"></i>
                    <span>Purchase Orders</span>
                </a>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN: Profile, Time & Notifications -->
    <div class="db-col-right">
        
        <!-- Profile Card -->
        <div class="d-card d-profile">
            <div class="d-profile-avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 2)) ?></div>
            <div class="d-profile-name"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></div>
            <span class="d-profile-role"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Staff')) ?></span>
            <p class="d-profile-greeting">
                Good <?= date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening') ?> 👋
            </p>
            <div class="d-profile-divider"></div>
            <div class="d-profile-stat">
                <span>Session</span>
                <strong><?= date('g:i A') ?></strong>
            </div>
            <div class="d-profile-stat">
                <span>Today</span>
                <strong><?= date('d M Y') ?></strong>
            </div>
            <a href="<?= APP_URL ?>/auth/logout" class="d-profile-logout-btn">
                <i class="ph ph-sign-out"></i>
                <span>Sign Out</span>
            </a>
        </div>

        <!-- Date / Time Card -->
        <div class="d-card d-datetime">
            <div class="d-day" id="dashDay"></div>
            <div class="d-time" id="dashTime">--:-- <span>--</span></div>
            <div class="d-date" id="dashDate"></div>
        </div>

        <!-- Notifications Card -->
        <div class="d-card d-notif">
            <div class="d-notif-header">
                <span class="d-notif-title">Notifications</span>
                <?php if ($notifCount > 0): ?>
                    <span class="d-notif-count"><?= $notifCount ?></span>
                <?php endif; ?>
            </div>
            <?php if ($notifCount === 0): ?>
                <div class="d-notif-empty">
                    <i class="ph ph-bell-slash"></i>
                    All caught up!<br>No new notifications.
                </div>
            <?php else: ?>
                <div class="d-notif-item">
                    <span class="d-notif-dot"></span>
                    <span class="d-notif-text">You have <?= $notifCount ?> unread notification<?= $notifCount > 1 ? 's' : '' ?></span>
                </div>
            <?php endif; ?>
            <a href="<?= APP_URL ?>/notification" class="d-notif-link">View All Notifications</a>
        </div>

    </div>

</div>

<script>
/* ── Live Clock ── */
function updateClock() {
    const now = new Date();
    const hh = now.getHours(), mm = now.getMinutes();
    const ampm = hh >= 12 ? 'PM' : 'AM';
    const h12 = hh % 12 || 12;
    const pad = n => String(n).padStart(2,'0');
    const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const el = document.getElementById('dashTime');
    if (el) el.innerHTML = `${pad(h12)}:${pad(mm)} <span>${ampm}</span>`;
    const de = document.getElementById('dashDate');
    if (de) de.textContent = `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
    const dy = document.getElementById('dashDay');
    if (dy) dy.textContent = days[now.getDay()];
}
updateClock(); setInterval(updateClock, 1000);

/* ── Unsplash Wallpaper (hourly cache) ── */
(async function loadWallpaper() {
    const KEY = 'curtiss_wp', TIME_KEY = 'curtiss_wp_ts', ONE_HOUR = 3600000;
    const bg = document.getElementById('dashBg');
    if (!bg) return;
    const cached = localStorage.getItem(KEY);
    const ts = parseInt(localStorage.getItem(TIME_KEY) || '0');
    if (cached && (Date.now() - ts) < ONE_HOUR) {
        applyBg(cached); return;
    }
    try {
        const queries = ['luxury office interior','modern workspace minimal','architecture interior light','business premium interior'];
        const q = queries[Math.floor(Date.now() / ONE_HOUR) % queries.length];
        const res = await fetch(`https://api.unsplash.com/photos/random?client_id=l9G-Db3ETQ2zxJN1viWaIpgPjshWK2z9gmczP3kWlX4&orientation=landscape&query=${encodeURIComponent(q)}&content_filter=high`);
        const data = await res.json();
        const url = data.urls?.regular || data.urls?.full;
        if (url) { localStorage.setItem(KEY, url); localStorage.setItem(TIME_KEY, Date.now().toString()); applyBg(url); }
    } catch(e) { bg.classList.add('ready'); }
    function applyBg(url) {
        const img = new Image();
        img.onload = () => { bg.style.backgroundImage = `url('${url}')`; bg.classList.add('ready'); };
        img.onerror = () => bg.classList.add('ready');
        img.src = url;
    }
})();
</script>