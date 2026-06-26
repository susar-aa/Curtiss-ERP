<?php /* Premium Dashboard */ ?>
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
    background: linear-gradient(150deg, rgba(8,8,22,.52) 0%, rgba(8,8,22,.28) 55%, rgba(8,8,22,.48) 100%);
}
#dashBg.ready { opacity: 1; }

/* ── CARD BASE ── */
.d-card {
    background: rgba(255,255,255,0.10);
    backdrop-filter: blur(28px);
    -webkit-backdrop-filter: blur(28px);
    border: 1px solid rgba(255,255,255,0.16);
    border-radius: 22px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.22);
    overflow: hidden;
    transition: transform .25s, box-shadow .25s;
}
.d-card:hover { transform: translateY(-3px); box-shadow: 0 16px 56px rgba(0,0,0,0.3); }

/* ── TOP BAR ── */
.db-topbar {
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 22px; flex-wrap: wrap;
}
.db-search {
    display: flex; align-items: center; gap: 10px;
    flex: 1; min-width: 200px; max-width: 440px; height: 46px;
    background: rgba(255,255,255,0.13);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 23px; padding: 0 18px;
    backdrop-filter: blur(20px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.14);
    cursor: text; transition: background .2s, box-shadow .2s;
}
.db-search:hover, .db-search:focus-within {
    background: rgba(255,255,255,0.2);
    box-shadow: 0 6px 30px rgba(79,70,229,.25);
}
.db-search i { color: rgba(255,255,255,.65); font-size: 16px; }
.db-search input {
    border: none; background: transparent; outline: none;
    font-size: 14px; color: #fff; width: 100%; font-family: inherit;
}
.db-search input::placeholder { color: rgba(255,255,255,.45); }
.db-actions { display: flex; align-items: center; gap: 8px; margin-left: auto; }
.db-icon-btn {
    width: 46px; height: 46px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,.82); font-size: 19px; text-decoration: none;
    background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.18);
    backdrop-filter: blur(16px);
    box-shadow: 0 4px 16px rgba(0,0,0,.14);
    transition: background .18s, color .18s; position: relative; cursor: pointer;
}
.db-icon-btn:hover { background: rgba(255,255,255,.22); color: #fff; }
.db-icon-btn.red { color: rgba(255,130,130,.9); }
.db-icon-btn.red:hover { background: rgba(239,68,68,.22); }
.db-nbadge {
    background: #ef4444; color: #fff; border-radius: 10px;
    padding: 1px 5px; font-size: 9px; font-weight: 700;
    position: absolute; top: 2px; right: 2px;
    border: 2px solid rgba(0,0,0,.15);
}
.db-user-pill {
    display: flex; align-items: center; gap: 10px;
    padding: 6px 16px 6px 6px;
    background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.18);
    border-radius: 24px; backdrop-filter: blur(16px);
    box-shadow: 0 4px 16px rgba(0,0,0,.14); cursor: default;
}
.db-avatar {
    width: 34px; height: 34px; border-radius: 50%;
    background: linear-gradient(135deg,#667eea,#764ba2);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 13px; font-weight: 700;
}
.db-uname { font-size: 13px; font-weight: 600; color: rgba(255,255,255,.92); }
.db-urole { font-size: 11px; color: rgba(255,255,255,.50); }

/* ── GRID ── */
.db-grid {
    display: grid;
    grid-template-columns: 260px 1fr 280px;
    grid-template-rows: auto auto;
    gap: 16px;
}
.db-col-left  { display: flex; flex-direction: column; gap: 16px; grid-column: 1; }
.db-col-mid   { display: flex; flex-direction: column; gap: 16px; grid-column: 2; }
.db-col-right { display: flex; flex-direction: column; gap: 16px; grid-column: 3; }

/* ── PROFILE CARD ── */
.d-profile {
    padding: 28px 22px 24px;
    display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px;
}
.d-profile-avatar {
    width: 72px; height: 72px; border-radius: 50%;
    background: linear-gradient(135deg,#667eea,#764ba2);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 26px; font-weight: 700;
    box-shadow: 0 6px 24px rgba(102,126,234,.45);
    margin-bottom: 4px;
}
.d-profile-name { font-size: 18px; font-weight: 700; color: #fff; }
.d-profile-role {
    font-size: 12px; color: rgba(255,255,255,.55);
    background: rgba(255,255,255,.1); border-radius: 20px;
    padding: 3px 12px; display: inline-block;
}
.d-profile-greeting { font-size: 13px; color: rgba(255,255,255,.7); margin-top: 4px; }
.d-profile-divider { width: 100%; height: 1px; background: rgba(255,255,255,.1); margin: 4px 0; }
.d-profile-stat {
    display: flex; justify-content: space-between; width: 100%;
    font-size: 12px; color: rgba(255,255,255,.6);
}
.d-profile-stat strong { color: rgba(255,255,255,.9); font-weight: 600; }

/* ── DATE / TIME CARD ── */
.d-datetime {
    padding: 24px 22px;
    display: flex; flex-direction: column; justify-content: center; gap: 4px;
}
.d-time { font-size: 44px; font-weight: 300; color: #fff; letter-spacing: -2px; line-height: 1; }
.d-time span { font-size: 18px; font-weight: 400; opacity: .6; }
.d-date { font-size: 15px; color: rgba(255,255,255,.65); font-weight: 500; margin-top: 8px; }
.d-day  { font-size: 12px; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .8px; }

/* ── NOTIFICATION CARD ── */
.d-notif { padding: 22px; }
.d-notif-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px;
}
.d-notif-title { font-size: 14px; font-weight: 600; color: rgba(255,255,255,.9); }
.d-notif-count {
    background: #ef4444; color: #fff; border-radius: 12px;
    padding: 2px 8px; font-size: 11px; font-weight: 700;
}
.d-notif-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,.07);
}
.d-notif-item:last-child { border-bottom: none; }
.d-notif-dot { width: 8px; height: 8px; border-radius: 50%; background: #667eea; flex-shrink: 0; }
.d-notif-dot.orange { background: #f59e0b; }
.d-notif-dot.green  { background: #10b981; }
.d-notif-text { font-size: 12px; color: rgba(255,255,255,.7); line-height: 1.4; }
.d-notif-empty {
    text-align: center; padding: 28px 0;
    color: rgba(255,255,255,.35); font-size: 13px;
}
.d-notif-empty i { font-size: 32px; display: block; margin-bottom: 8px; }
.d-notif-link {
    display: block; text-align: center; margin-top: 14px;
    font-size: 12px; color: rgba(102,126,234,.9); text-decoration: none;
    padding: 8px; border-radius: 10px;
    background: rgba(102,126,234,.1); transition: background .18s;
}
.d-notif-link:hover { background: rgba(102,126,234,.2); }

/* ── QUICK ACCESS CARD ── */
.d-quick { padding: 22px; }
.d-quick-header { font-size: 13px; font-weight: 600; color: rgba(255,255,255,.7); margin-bottom: 16px; letter-spacing: .3px; }
.d-quick-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.d-qbtn {
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    padding: 14px 8px; border-radius: 14px;
    text-decoration: none; color: rgba(255,255,255,.82);
    background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.09);
    transition: background .18s, transform .18s, border-color .18s;
    text-align: center;
}
.d-qbtn:hover {
    background: rgba(79,70,229,.22); border-color: rgba(79,70,229,.35);
    color: #fff; transform: translateY(-2px);
}
.d-qbtn i { font-size: 22px; }
.d-qbtn span { font-size: 11px; font-weight: 500; line-height: 1.3; }

/* ── STATUS CARD ── */
.d-status { padding: 22px; }
.d-status-title { font-size: 13px; font-weight: 600; color: rgba(255,255,255,.7); margin-bottom: 14px; }
.d-status-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,.07);
    font-size: 13px;
}
.d-status-row:last-child { border-bottom: none; }
.d-status-label { color: rgba(255,255,255,.65); display: flex; align-items: center; gap: 8px; }
.d-status-label i { font-size: 15px; }
.d-status-val { font-weight: 600; color: rgba(255,255,255,.9); }
.d-badge { border-radius: 20px; padding: 3px 10px; font-size: 11px; font-weight: 600; }
.d-badge.green { background: rgba(16,185,129,.2); color: #6ee7b7; }
.d-badge.orange { background: rgba(245,158,11,.2); color: #fcd34d; }

@media (max-width: 1100px) {
    .db-grid { grid-template-columns: 1fr 1fr; }
    .db-col-mid { grid-column: 1 / 3; order: -1; }
}
@media (max-width: 700px) {
    .db-grid { grid-template-columns: 1fr; }
    .db-col-left, .db-col-mid, .db-col-right { grid-column: 1; }
    .d-quick-grid { grid-template-columns: repeat(3,1fr); }
}
</style>

<!-- Background Layer -->
<div id="dashBg"></div>

<!-- Top Bar -->
<div class="db-topbar">
    <div class="db-search" onclick="this.querySelector('input').focus()">
        <i class="ph ph-magnifying-glass"></i>
        <input type="text" id="dashSearch" placeholder="Search customers, invoices, products..." autocomplete="off">
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
        <div class="db-user-pill">
            <div class="db-avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 2)) ?></div>
            <div>
                <div class="db-uname"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></div>
                <div class="db-urole"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Staff')) ?></div>
            </div>
        </div>
        <a href="<?= APP_URL ?>/auth/logout" class="db-icon-btn red" title="Logout">
            <i class="ph ph-sign-out"></i>
        </a>
    </div>
</div>

<!-- Cards Grid -->
<div class="db-grid">

    <!-- LEFT COLUMN -->
    <div class="db-col-left">

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
        </div>

        <!-- Date / Time Card -->
        <div class="d-card d-datetime">
            <div class="d-day" id="dashDay"></div>
            <div class="d-time" id="dashTime">--:-- <span>--</span></div>
            <div class="d-date" id="dashDate"></div>
        </div>

        <!-- System Status Card -->
        <div class="d-card d-status">
            <div class="d-status-title">System Status</div>
            <div class="d-status-row">
                <span class="d-status-label"><i class="ph ph-database"></i> Database</span>
                <span class="d-badge green">Online</span>
            </div>
            <div class="d-status-row">
                <span class="d-status-label"><i class="ph ph-cloud-arrow-up"></i> Sync</span>
                <span class="d-badge green">Active</span>
            </div>
            <div class="d-status-row">
                <span class="d-status-label"><i class="ph ph-shield-check"></i> Security</span>
                <span class="d-badge green">Secured</span>
            </div>
        </div>

    </div>

    <!-- MIDDLE COLUMN -->
    <div class="db-col-mid">

        <!-- Quick Access Card -->
        <div class="d-card d-quick">
            <div class="d-quick-header">Quick Access</div>
            <div class="d-quick-grid">
                <a href="<?= APP_URL ?>/sales" class="d-qbtn"><i class="ph ph-credit-card"></i><span>Invoices</span></a>
                <a href="<?= APP_URL ?>/salesorder" class="d-qbtn"><i class="ph ph-clipboard-text"></i><span>Sales Orders</span></a>
                <a href="<?= APP_URL ?>/customer" class="d-qbtn"><i class="ph ph-users"></i><span>Customers</span></a>
                <a href="<?= APP_URL ?>/inventory" class="d-qbtn"><i class="ph ph-package"></i><span>Inventory</span></a>
                <a href="<?= APP_URL ?>/purchase" class="d-qbtn"><i class="ph ph-shopping-cart"></i><span>Purchase Orders</span></a>
                <a href="<?= APP_URL ?>/expenses" class="d-qbtn"><i class="ph ph-receipt"></i><span>Enter Bills</span></a>
                <a href="<?= APP_URL ?>/banking" class="d-qbtn"><i class="ph ph-bank"></i><span>Banking</span></a>
                <a href="<?= APP_URL ?>/hrm" class="d-qbtn"><i class="ph ph-user-circle-gear"></i><span>Employees</span></a>
                <a href="<?= APP_URL ?>/report" class="d-qbtn"><i class="ph ph-chart-line-up"></i><span>Reports</span></a>
                <a href="<?= APP_URL ?>/crm" class="d-qbtn"><i class="ph ph-briefcase"></i><span>CRM</span></a>
                <a href="<?= APP_URL ?>/accounting/coa" class="d-qbtn"><i class="ph ph-notebook"></i><span>Chart of Accts</span></a>
                <a href="<?= APP_URL ?>/settings" class="d-qbtn"><i class="ph ph-gear"></i><span>Settings</span></a>
            </div>
        </div>

        <!-- More Quick Access Row 2 -->
        <div class="d-card d-quick" style="padding: 18px 22px;">
            <div class="d-quick-header" style="margin-bottom:12px;">Operations</div>
            <div class="d-quick-grid" style="grid-template-columns: repeat(5,1fr);">
                <a href="<?= APP_URL ?>/estimate" class="d-qbtn"><i class="ph ph-file-text"></i><span>Estimates</span></a>
                <a href="<?= APP_URL ?>/creditnote" class="d-qbtn"><i class="ph ph-arrow-counter-clockwise"></i><span>Refunds</span></a>
                <a href="<?= APP_URL ?>/delivery" class="d-qbtn"><i class="ph ph-truck"></i><span>Deliveries</span></a>
                <a href="<?= APP_URL ?>/cheque" class="d-qbtn"><i class="ph ph-signature"></i><span>Cheques</span></a>
                <a href="<?= APP_URL ?>/reptracking" class="d-qbtn"><i class="ph ph-map-pin"></i><span>Rep Tracking</span></a>
            </div>
        </div>

    </div>

    <!-- RIGHT COLUMN -->
    <div class="db-col-right">

        <!-- Notifications Card -->
        <div class="d-card d-notif">
            <div class="d-notif-header">
                <span class="d-notif-title"><i class="ph ph-bell" style="margin-right:6px;"></i>Notifications</span>
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
            <a href="<?= APP_URL ?>/notification" class="d-notif-link">View All Notifications →</a>
        </div>

        <!-- Store Link Card -->
        <?php if (!empty($storeUrl)): ?>
        <div class="d-card" style="padding:22px;">
            <div class="d-status-title" style="margin-bottom:14px;"><i class="ph ph-storefront" style="margin-right:6px;"></i>E-Commerce Store</div>
            <p style="font-size:12px;color:rgba(255,255,255,.55);margin-bottom:14px;">Access your online store and manage online orders.</p>
            <a href="<?= htmlspecialchars($storeUrl) ?>" target="_blank"
               style="display:flex;align-items:center;justify-content:center;gap:8px;padding:10px;border-radius:12px;background:rgba(79,70,229,.18);border:1px solid rgba(79,70,229,.3);color:#a5b4fc;text-decoration:none;font-size:13px;font-weight:600;transition:background .18s;">
                <i class="ph ph-arrow-square-out"></i> Open Store
            </a>
        </div>
        <?php endif; ?>

        <!-- Logout Card -->
        <div class="d-card" style="padding:22px;">
            <div class="d-status-title" style="margin-bottom:14px;"><i class="ph ph-user" style="margin-right:6px;"></i>Account</div>
            <div class="d-status-row">
                <span class="d-status-label"><i class="ph ph-user-circle"></i> User</span>
                <strong style="color:rgba(255,255,255,.85);font-size:13px;"><?= htmlspecialchars($_SESSION['username'] ?? '') ?></strong>
            </div>
            <div class="d-status-row">
                <span class="d-status-label"><i class="ph ph-lock-key"></i> Role</span>
                <span class="d-badge orange"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Staff')) ?></span>
            </div>
            <a href="<?= APP_URL ?>/auth/logout"
               style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:14px;padding:10px;border-radius:12px;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);color:rgba(255,130,130,.9);text-decoration:none;font-size:13px;font-weight:600;transition:background .18s;">
                <i class="ph ph-sign-out"></i> Sign Out
            </a>
        </div>

    </div>

</div>

<script>
/* ── Live Clock ── */
function updateClock() {
    const now = new Date();
    const hh = now.getHours(), mm = now.getMinutes(), ss = now.getSeconds();
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