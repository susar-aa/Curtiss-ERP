<?php /* Premium Dashboard – compact no-scroll */ ?>
<style>
/* ── BACKGROUND ── */
#dashBg{position:fixed;inset:0;z-index:-2;background:#0f0f1a;background-size:cover;background-position:center;transition:opacity 1.2s ease;opacity:0}
#dashBg::after{content:'';position:absolute;inset:0;background:linear-gradient(150deg,rgba(8,8,22,.62) 0%,rgba(8,8,22,.32) 55%,rgba(8,8,22,.52) 100%)}
#dashBg.ready{opacity:1}

/* ── FILL viewport without scroll ── */
.dash-root{
    display:flex;flex-direction:column;
    height:calc(100vh - 82px - 56px - 16px); /* viewport minus nav, recent-bar, padding */
    overflow:hidden;
    gap:10px;
}

/* ── TOPBAR ── */
.db-topbar{
    display:flex;align-items:center;gap:10px;
    flex-shrink:0;
}
.db-search{
    display:flex;align-items:center;gap:8px;
    flex:1;max-width:460px;height:38px;
    background:rgba(255,255,255,0.10);
    border:1px solid rgba(255,255,255,0.16);
    border-radius:19px;padding:0 14px;
    backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
    box-shadow:0 4px 16px rgba(0,0,0,0.12);
    cursor:text;transition:background .2s,box-shadow .2s,border-color .2s;
}
.db-search:hover,.db-search:focus-within{
    background:rgba(255,255,255,0.16);
    border-color:rgba(79,70,229,0.4);
    box-shadow:0 4px 24px rgba(79,70,229,.2);
}
.db-search i{color:rgba(255,255,255,.55);font-size:15px;flex-shrink:0}
.db-search input{border:none;background:transparent;outline:none;font-size:13px;color:#fff;width:100%;font-family:inherit}
.db-search input::placeholder{color:rgba(255,255,255,.4)}
.db-kbd-hint{
    font-size:10px;color:rgba(255,255,255,.3);
    background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.1);
    border-radius:5px;padding:2px 5px;font-family:monospace;flex-shrink:0;
}
.db-actions{display:flex;align-items:center;gap:6px;margin-left:auto}
.db-icon-btn{
    width:38px;height:38px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    color:rgba(255,255,255,.82);font-size:17px;text-decoration:none;
    background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.15);
    backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);
    box-shadow:0 4px 12px rgba(0,0,0,.12);
    transition:all .18s;position:relative;cursor:pointer;
}
.db-icon-btn:hover{background:rgba(79,70,229,.2);border-color:rgba(79,70,229,.35);color:#fff}
.db-nbadge{
    background:#ef4444;color:#fff;border-radius:9px;
    padding:1px 4px;font-size:8px;font-weight:700;
    position:absolute;top:1px;right:1px;
    border:2px solid rgba(10,10,20,.4);
}

/* ── MAIN GRID ── */
.db-grid{
    display:grid;
    grid-template-columns:1fr 300px;
    gap:12px;
    flex:1;
    min-height:0; /* crucial: allow grid to shrink */
}

/* ── CARD BASE – glassmorphism ── */
.d-card{
    background:rgba(255,255,255,0.08);
    backdrop-filter:blur(28px);-webkit-backdrop-filter:blur(28px);
    border:1px solid rgba(255,255,255,0.13);
    border-radius:18px;
    box-shadow:0 8px 32px rgba(0,0,0,0.22);
    overflow:hidden;
    transition:transform .22s,box-shadow .22s,border-color .22s;
}
.d-card:hover{transform:translateY(-2px);box-shadow:0 12px 40px rgba(0,0,0,0.3);border-color:rgba(255,255,255,.20)}

/* ── LEFT: Quick Access ── */
.db-col-left{display:flex;flex-direction:column;min-height:0}
.d-quick{padding:16px 18px;display:flex;flex-direction:column;height:100%}
.d-quick-header{
    font-size:10px;font-weight:700;color:rgba(255,255,255,.5);
    letter-spacing:1.2px;text-transform:uppercase;
    margin-bottom:12px;flex-shrink:0;
}
.d-quick-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:8px;
    flex:1;
    align-content:start;
}
.d-qbtn{
    display:flex;flex-direction:row;align-items:center;gap:10px;
    padding:10px 12px;border-radius:12px;
    text-decoration:none;color:rgba(255,255,255,.85);
    background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.09);
    backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
    transition:all .18s ease;
    min-height:0;
}
.d-qbtn:hover{
    background:rgba(79,70,229,.18);border-color:rgba(79,70,229,.35);
    color:#fff;transform:translateY(-1px);
    box-shadow:0 4px 16px rgba(79,70,229,0.15);
}
.d-qbtn-icon{
    width:32px;height:32px;border-radius:9px;
    background:rgba(255,255,255,.08);
    display:flex;align-items:center;justify-content:center;
    flex-shrink:0;font-size:17px;
    transition:transform .18s;
}
.d-qbtn:hover .d-qbtn-icon{transform:scale(1.08)}
.d-qbtn span{font-size:12px;font-weight:600;line-height:1.25;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

/* ── RIGHT COLUMN ── */
.db-col-right{display:flex;flex-direction:column;gap:10px;min-height:0}

/* ── PROFILE CARD ── */
.d-profile{
    padding:16px 18px;
    display:flex;flex-direction:column;align-items:center;text-align:center;gap:8px;
    flex-shrink:0;
}
.d-profile-avatar{
    width:58px;height:58px;border-radius:50%;
    background:linear-gradient(135deg,#667eea,#764ba2);
    display:flex;align-items:center;justify-content:center;
    color:#fff;font-size:22px;font-weight:700;
    box-shadow:0 4px 18px rgba(102,126,234,.4);
}
.d-profile-name{font-size:15px;font-weight:700;color:#fff;margin-top:2px}
.d-profile-role{
    font-size:10px;color:rgba(139,92,246,.9);font-weight:700;
    background:rgba(139,92,246,0.15);border-radius:20px;
    padding:3px 12px;text-transform:uppercase;letter-spacing:.5px;
}
.d-profile-greeting{font-size:12px;color:rgba(255,255,255,.55)}
.d-profile-divider{width:100%;height:1px;background:rgba(255,255,255,.1);margin:4px 0}
.d-profile-row{
    display:flex;justify-content:space-between;align-items:center;width:100%;
    font-size:11px;color:rgba(255,255,255,.5);
}
.d-profile-row strong{color:rgba(255,255,255,.85);font-weight:600}
.d-profile-logout{
    display:flex;align-items:center;justify-content:center;gap:7px;
    width:100%;margin-top:6px;padding:8px;border-radius:10px;
    background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.22);
    color:rgba(239,68,68,.88);text-decoration:none;
    font-size:12px;font-weight:600;transition:all .18s;
}
.d-profile-logout:hover{background:rgba(239,68,68,.2);box-shadow:0 4px 12px rgba(239,68,68,.15)}

/* ── CLOCK CARD ── */
.d-datetime{
    padding:14px 18px;
    display:flex;flex-direction:column;justify-content:center;gap:2px;
    flex-shrink:0;
}
.d-day{font-size:10px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.8px;font-weight:600}
.d-time{font-size:32px;font-weight:300;color:#fff;letter-spacing:-1px;line-height:1}
.d-time span{font-size:14px;font-weight:400;opacity:.6}
.d-date{font-size:13px;color:rgba(255,255,255,.65);font-weight:500;margin-top:3px}

/* ── NOTIFICATIONS CARD ── */
.d-notif{padding:14px 18px;display:flex;flex-direction:column;flex:1;min-height:0}
.d-notif-header{
    display:flex;align-items:center;justify-content:space-between;
    margin-bottom:10px;flex-shrink:0;
}
.d-notif-title{font-size:10px;font-weight:700;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:1px}
.d-notif-count{background:#ef4444;color:#fff;border-radius:10px;padding:2px 7px;font-size:10px;font-weight:700}
.d-notif-item{
    display:flex;align-items:center;gap:8px;
    padding:8px 0;border-bottom:1px solid rgba(255,255,255,.07);
}
.d-notif-item:last-child{border-bottom:none}
.d-notif-dot{width:7px;height:7px;border-radius:50%;background:#667eea;flex-shrink:0}
.d-notif-text{font-size:12px;color:rgba(255,255,255,.65);line-height:1.4}
.d-notif-empty{
    flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
    color:rgba(255,255,255,.3);font-size:12px;gap:6px;
}
.d-notif-empty i{font-size:24px;opacity:.5}
.d-notif-link{
    display:block;text-align:center;margin-top:auto;padding-top:10px;
    font-size:11px;color:rgba(102,126,234,.9);text-decoration:none;
    padding:7px;border-radius:8px;background:rgba(79,70,229,.1);font-weight:600;
    transition:background .18s;flex-shrink:0;margin-top:10px;
}
.d-notif-link:hover{background:rgba(79,70,229,.18)}
</style>

<!-- Background -->
<div id="dashBg"></div>

<div class="dash-root">

    <!-- Top Bar -->
    <div class="db-topbar">
        <div class="db-search" onclick="this.querySelector('input').focus()">
            <i class="ph ph-magnifying-glass"></i>
            <input type="text" id="dashSearch" placeholder="Search anything... (Ctrl+K)" autocomplete="off" readonly onfocus="this.blur();openCmdPalette()">
        </div>
        <span class="db-kbd-hint">Ctrl+K</span>
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

    <!-- Grid -->
    <div class="db-grid">

        <!-- LEFT: Quick Access -->
        <div class="db-col-left">
            <div class="d-card d-quick">
                <div class="d-quick-header">Quick Access</div>
                <div class="d-quick-grid">
                    <a href="<?= APP_URL ?>/sales/create" class="d-qbtn">
                        <div class="d-qbtn-icon" style="color:#3b82f6"><i class="ph ph-file-plus"></i></div>
                        <span>Create Invoice</span>
                    </a>
                    <a href="<?= APP_URL ?>/salesorder/create" class="d-qbtn">
                        <div class="d-qbtn-icon" style="color:#10b981"><i class="ph ph-file-text"></i></div>
                        <span>Sales Order</span>
                    </a>
                    <a href="<?= APP_URL ?>/customer" class="d-qbtn">
                        <div class="d-qbtn-icon" style="color:#6366f1"><i class="ph ph-users"></i></div>
                        <span>Customers</span>
                    </a>
                    <a href="<?= APP_URL ?>/supplier" class="d-qbtn">
                        <div class="d-qbtn-icon" style="color:#f59e0b"><i class="ph ph-factory"></i></div>
                        <span>Suppliers</span>
                    </a>
                    <a href="<?= APP_URL ?>/inventory" class="d-qbtn">
                        <div class="d-qbtn-icon" style="color:#ec4899"><i class="ph ph-package"></i></div>
                        <span>Inventory</span>
                    </a>
                    <a href="<?= APP_URL ?>/reptracking" class="d-qbtn">
                        <div class="d-qbtn-icon" style="color:#06b6d4"><i class="ph ph-map-pin"></i></div>
                        <span>Route Control</span>
                    </a>
                    <a href="<?= APP_URL ?>/customerpayment" class="d-qbtn">
                        <div class="d-qbtn-icon" style="color:#14b8a6"><i class="ph ph-hand-coins"></i></div>
                        <span>Payments</span>
                    </a>
                    <a href="<?= APP_URL ?>/grn" class="d-qbtn">
                        <div class="d-qbtn-icon" style="color:#8b5cf6"><i class="ph ph-tray-arrow-down"></i></div>
                        <span>GRN</span>
                    </a>
                    <a href="<?= APP_URL ?>/purchase" class="d-qbtn">
                        <div class="d-qbtn-icon" style="color:#ef4444"><i class="ph ph-shopping-cart"></i></div>
                        <span>Purchase Orders</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- RIGHT: Profile + Clock + Notifications -->
        <div class="db-col-right">

            <!-- Profile -->
            <div class="d-card d-profile">
                <div class="d-profile-avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 2)) ?></div>
                <div class="d-profile-name"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></div>
                <span class="d-profile-role"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Staff')) ?></span>
                <p class="d-profile-greeting">Good <?= date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening') ?> 👋</p>
                <div class="d-profile-divider"></div>
                <div class="d-profile-row">
                    <span>Session</span><strong><?= date('g:i A') ?></strong>
                </div>
                <div class="d-profile-row">
                    <span>Today</span><strong><?= date('d M Y') ?></strong>
                </div>
                <a href="<?= APP_URL ?>/auth/logout" class="d-profile-logout">
                    <i class="ph ph-sign-out"></i> Sign Out
                </a>
            </div>

            <!-- Clock -->
            <div class="d-card d-datetime">
                <div class="d-day" id="dashDay"></div>
                <div class="d-time" id="dashTime">--:-- <span>--</span></div>
                <div class="d-date" id="dashDate"></div>
            </div>

            <!-- Notifications -->
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
                        <span>All caught up!</span>
                    </div>
                <?php else: ?>
                    <div class="d-notif-item">
                        <span class="d-notif-dot"></span>
                        <span class="d-notif-text"><?= $notifCount ?> unread notification<?= $notifCount > 1 ? 's' : '' ?></span>
                    </div>
                <?php endif; ?>
                <a href="<?= APP_URL ?>/notification" class="d-notif-link">View All →</a>
            </div>

        </div>
    </div>
</div>

<script>
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

(async function loadWallpaper() {
    const KEY = 'curtiss_wp', TIME_KEY = 'curtiss_wp_ts', ONE_HOUR = 3600000;
    const bg = document.getElementById('dashBg');
    if (!bg) return;
    const cached = localStorage.getItem(KEY);
    const ts = parseInt(localStorage.getItem(TIME_KEY) || '0');
    if (cached && (Date.now() - ts) < ONE_HOUR) { applyBg(cached); return; }
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