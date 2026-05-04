<?php
// Ensure auth functions exist
if (!function_exists('hasAccess')) {
    require_once __DIR__ . '/auth_check.php';
}

$cur = basename($_SERVER['PHP_SELF']);

// Sidebar is now purely for Quick Actions and Recent Pages
?>

<style>
/* ─── iOS Sidebar Base ─────────────────────────────── */
#sidebarMenu { 
    width: 280px; 
    background: #F9FAFB; 
    border-left: 1px solid rgba(60,60,67,0.1); 
    overflow-y: auto; 
    overflow-x: hidden; 
    transition: width 0.3s cubic-bezier(0.2, 0, 0, 1); /* Smooth resize */
    scrollbar-width: none; /* Hide scrollbar for cleaner look */
}
#sidebarMenu::-webkit-scrollbar { display: none; }

.sb-inner { 
    padding: 16px 12px 40px; 
    display: flex;
    flex-direction: column;
}

/* Typography & Layout */
.sb-group-label { 
    font-size: 0.65rem; 
    font-weight: 700; 
    letter-spacing: 0.08em; 
    text-transform: uppercase; 
    color: rgba(60,60,67,0.4); 
    padding: 16px 10px 8px; 
    display: block; 
    transition: opacity 0.2s ease;
    white-space: nowrap;
}

.sb-link { 
    display: flex; 
    align-items: center; 
    gap: 12px; 
    padding: 8px 10px; 
    border-radius: 12px; 
    text-decoration: none; 
    font-size: 0.9rem; 
    font-weight: 600; 
    color: rgba(0,0,0,0.85); 
    transition: all 0.2s ease; 
    margin-bottom: 4px; 
    cursor: pointer;
    white-space: nowrap;
}
.sb-link:hover { background: rgba(60,60,67,0.06); color: #000; }
.sb-link.active { background: rgba(48,200,138,0.12); color: #1A8A5A; }
.sb-link .sb-icon { 
    width: 32px; 
    height: 32px; 
    border-radius: 8px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 1rem; 
    flex-shrink: 0; 
    transition: background 0.2s, color 0.2s; 
}
.sb-link .sb-label { flex: 1; transition: opacity 0.2s ease; }
.sb-link .sb-chevron { font-size: 0.7rem; color: rgba(60,60,67,0.3); transition: transform 0.3s ease; flex-shrink: 0; }
.sb-link.section-open .sb-chevron { transform: rotate(180deg); }

/* Color Themes for Icons */
.icon-green  { background: rgba(48,200,138,0.12); color: #25A872; }
.icon-blue   { background: rgba(0,122,255,0.10);  color: #007AFF; }
.icon-indigo { background: rgba(88,86,214,0.10);  color: #5856D6; }
.icon-orange { background: rgba(255,149,0,0.10);  color: #E07800; }
.icon-red    { background: rgba(255,59,48,0.10);  color: #CC2200; }
.icon-teal   { background: rgba(48,176,199,0.10); color: #1A8A9A; }
.icon-purple { background: rgba(175,82,222,0.10); color: #8B2BAA; }
.icon-gray   { background: rgba(60,60,67,0.08);   color: rgba(60,60,67,0.6); }

.sb-link.active .sb-icon { background: #30C88A; color: #fff; box-shadow: 0 2px 8px rgba(48,200,138,0.3); }

/* Submenus */
.sb-sub { list-style: none; padding: 4px 0 4px 44px; margin: 0 0 6px; }
.sb-sub a { 
    display: flex; 
    align-items: center; 
    gap: 8px; 
    padding: 8px 10px; 
    border-radius: 8px; 
    text-decoration: none; 
    font-size: 0.85rem; 
    font-weight: 500; 
    color: rgba(60,60,67,0.75); 
    transition: all 0.2s ease; 
    margin-bottom: 2px; 
    white-space: nowrap;
}
.sb-sub a:hover { background: rgba(60,60,67,0.06); color: #000; }
.sb-sub a.active { color: #1A8A5A; font-weight: 700; background: rgba(48,200,138,0.05); }
.sb-sub a .sub-dot { width: 6px; height: 6px; border-radius: 50%; background: rgba(60,60,67,0.25); flex-shrink: 0; transition: background 0.2s; }
.sb-sub a.active .sub-dot { background: #30C88A; box-shadow: 0 0 4px rgba(48,200,138,0.5); }

.sb-divider { height: 1px; background: rgba(60,60,67,0.08); margin: 16px 12px; }

/* ─── iOS Sidebar MINIMIZED STATE ─────────────────────────────── */
#sidebarMenu.minimized { 
    width: 80px; 
}
#sidebarMenu.minimized .sb-inner {
    padding: 16px 8px 40px; /* Tighter padding */
    align-items: center;
}
#sidebarMenu.minimized .sb-group-label,
#sidebarMenu.minimized .sb-label,
#sidebarMenu.minimized .sb-chevron { 
    display: none; 
    opacity: 0;
}
#sidebarMenu.minimized .sb-link { 
    padding: 10px; 
    justify-content: center; 
    width: 100%;
    margin-bottom: 8px;
}
#sidebarMenu.minimized .sb-link .sb-icon {
    width: 36px;
    height: 36px;
    font-size: 1.1rem;
}
#sidebarMenu.minimized .collapse { 
    display: none !important; /* Hide submenus completely */
}
#sidebarMenu.minimized .sb-divider {
    margin: 16px 0;
    width: 40px;
}
.sidebar {
    order: 2; /* Move sidebar to the right */
}
</style>

<nav id="sidebarMenu" class="sidebar d-md-block">
    <div class="position-sticky sb-inner">

        <!-- Quick Actions Section -->
        <span class="sb-group-label">Quick Actions</span>

        <?php if(hasAccess('create_order.php')): ?>
        <a class="sb-link" href="create_order.php">
            <span class="sb-icon icon-green"><i class="bi bi-cart-plus-fill"></i></span>
            <span class="sb-label">New POS Order</span>
        </a>
        <?php endif; ?>
        
        <?php if(hasAccess('create_grn.php')): ?>
        <a class="sb-link" href="create_grn.php">
            <span class="sb-icon icon-orange"><i class="bi bi-box-arrow-in-down"></i></span>
            <span class="sb-label">Receive Stock</span>
        </a>
        <?php endif; ?>

        <?php if(hasAccess('expenses.php')): ?>
        <a class="sb-link" href="expenses.php">
            <span class="sb-icon icon-red"><i class="bi bi-wallet2"></i></span>
            <span class="sb-label">Add Expense</span>
        </a>
        <?php endif; ?>
        
        <?php if(hasAccess('orders_list.php')): ?>
        <a class="sb-link" href="orders_list.php">
            <span class="sb-icon icon-blue"><i class="bi bi-receipt"></i></span>
            <span class="sb-label">Sales History</span>
        </a>
        <?php endif; ?>
        
        <?php if(hasAccess('products.php')): ?>
        <a class="sb-link" href="products.php">
            <span class="sb-icon icon-indigo"><i class="bi bi-boxes"></i></span>
            <span class="sb-label">Inventory</span>
        </a>
        <?php endif; ?>
        
        <?php if(hasAccess('customers.php')): ?>
        <a class="sb-link" href="customers.php">
            <span class="sb-icon icon-teal"><i class="bi bi-people-fill"></i></span>
            <span class="sb-label">Customers</span>
        </a>
        <?php endif; ?>

        <div class="sb-divider"></div>

        <!-- Recent Pages Section -->
        <span class="sb-group-label">Recent Pages</span>
        
        <?php if(empty($_SESSION['recent_pages'])): ?>
            <div style="padding: 10px; text-align: center; color: rgba(60,60,67,0.4); font-size: 0.8rem; font-weight: 500;">
                No recent activity.
            </div>
        <?php else: ?>
            <?php foreach($_SESSION['recent_pages'] as $page): ?>
                <?php if($page['url'] !== basename($_SERVER['PHP_SELF'])): ?>
                <a class="sb-link" href="<?php echo htmlspecialchars($page['url']); ?>" style="padding: 6px 10px;">
                    <span class="sb-icon icon-gray" style="width: 24px; height: 24px; font-size: 0.8rem;"><i class="bi bi-clock-history"></i></span>
                    <span class="sb-label" style="font-size: 0.85rem; color: rgba(60,60,67,0.8);"><?php echo htmlspecialchars($page['title']); ?></span>
                </a>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebarMenu');
    const links = sidebar.querySelectorAll('.sb-link');
    
    // Auto-maximize sidebar when a menu link is clicked while minimized
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            if (sidebar.classList.contains('minimized') || document.body.classList.contains('sidebar-minimized')) {
                // Remove minimized state first
                sidebar.classList.remove('minimized');
                document.body.classList.remove('sidebar-minimized');
            }
        });
    });

    // Update Chevron animation on Submenu Open/Close
    const collapses = document.querySelectorAll('.collapse');
    collapses.forEach(collapse => {
        collapse.addEventListener('show.bs.collapse', function () {
            // Ensure sidebar expands if a sub-menu is triggered while minimized
            if (sidebar.classList.contains('minimized') || document.body.classList.contains('sidebar-minimized')) {
                sidebar.classList.remove('minimized');
                document.body.classList.remove('sidebar-minimized');
            }
            const link = document.querySelector(`[data-bs-target="#${this.id}"]`);
            if(link) link.classList.add('section-open');
        });
        collapse.addEventListener('hide.bs.collapse', function () {
            const link = document.querySelector(`[data-bs-target="#${this.id}"]`);
            if(link) link.classList.remove('section-open');
        });
    });
});
</script>

<main id="mainContent" class="px-md-4 pb-5 flex-grow-1" style="transition: all 0.3s cubic-bezier(0.2, 0, 0, 1); order: 1;">