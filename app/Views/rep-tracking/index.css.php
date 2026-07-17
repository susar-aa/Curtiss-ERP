<style>
    /* Workspace Active Visual Density Optimizations */
    body.workspace-showing .app-container {
        margin-top: 68px !important;
        height: calc(100vh - 68px) !important;
    }
    body.workspace-showing .main-content {
        padding: 12px 16px 12px 16px !important;
        height: 100% !important;
        overflow: hidden !important;
    }
    body.workspace-showing .app-workspace {
        height: 100% !important;
        border-radius: var(--r-lg) !important;
    }

    /* Dots Menu styles */
    .dots-menu-container {
        position: relative;
        display: inline-block;
    }
    .dots-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 6px;
        font-size: 16px;
        color: var(--t-secondary);
        border-radius: 50%;
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
    }
    .dots-btn:hover {
        background: var(--c-fill);
        color: var(--t-primary);
    }
    .dots-dropdown {
        display: none;
        position: fixed;
        background: rgba(255, 255, 255, 0.78) !important;
        backdrop-filter: blur(20px) saturate(190%) !important;
        -webkit-backdrop-filter: blur(20px) saturate(190%) !important;
        border: 0.5px solid rgba(0, 0, 0, 0.15) !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.02) !important;
        z-index: 1001;
        min-width: 190px;
        padding: 6px;
        margin: 0;
        overflow: hidden;
    }
    .dots-dropdown.show {
        display: block;
    }
    .dots-dropdown-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-direction: row-reverse;
        gap: 12px;
        padding: 9px 12px;
        font-size: 14px;
        font-weight: 450;
        color: #0f172a !important;
        cursor: pointer;
        text-align: left;
        transition: background 0.12s;
        width: 100%;
        border: none;
        background: none;
        border-radius: 8px;
    }
    .dots-dropdown-item:hover {
        background: rgba(0, 0, 0, 0.05) !important;
    }
    .dots-dropdown-item i {
        font-size: 16px;
        color: rgba(15, 23, 42, 0.6) !important;
    }
    .dots-dropdown-item.danger {
        color: #ff3b30 !important;
    }
    .dots-dropdown-item.danger i {
        color: #ff3b30 !important;
    }
    .dots-dropdown-item.danger:hover {
        background: rgba(255, 59, 48, 0.08) !important;
    }
    .dots-dropdown-divider {
        height: 0.5px;
        background: rgba(0, 0, 0, 0.1);
        margin: 4px 6px;
    }
    
    @media (prefers-color-scheme: dark) {
        .dots-dropdown {
            background: rgba(28, 28, 30, 0.82) !important;
            border: 0.5px solid rgba(255, 255, 255, 0.15) !important;
        }
        .dots-dropdown-item {
            color: #f8fafc !important;
        }
        .dots-dropdown-item i {
            color: rgba(248, 250, 252, 0.6) !important;
        }
        .dots-dropdown-item:hover {
            background: rgba(255, 255, 255, 0.08) !important;
        }
        .dots-dropdown-divider {
            background: rgba(255, 255, 255, 0.1);
        }
    }

    /* ============================================================
       SF PRO + APPLE DESIGN LANGUAGE — REP TRACKING MODULE
       ============================================================ */

    :root {
        --c-bg:           #f2f2f7;
        --c-surface:      #ffffff;
        --c-surface2:     #f9f9fb;
        --c-fill:         rgba(120,120,128,0.08);
        --c-fill2:        rgba(120,120,128,0.12);
        --c-separator:    rgba(60,60,67,0.12);
        --c-separator2:   rgba(60,60,67,0.06);

        --c-blue:         #007aff;
        --c-blue-light:   #e5f2ff;
        --c-blue-mid:     #b3d6ff;
        --c-green:        #34c759;
        --c-green-light:  #e6f9ec;
        --c-orange:       #ff9500;
        --c-orange-light: #fff4e5;
        --c-red:          #ff3b30;
        --c-red-light:    #fff0ef;

        --primary:        #007aff;

        --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', 'Helvetica Neue', sans-serif;
        --f-mono:   ui-monospace, 'SF Mono', 'Menlo', 'Monaco', monospace;

        --t-primary:   #1c1c1e;
        --t-secondary: #636366;
        --t-tertiary:  #aeaeb2;
        --t-label:     #8e8e93;

        --shadow-xs:  0 1px 2px rgba(0,0,0,0.04);
        --shadow-sm:  0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
        --shadow-md:  0 8px 24px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
        --shadow-xl:  0 24px 48px rgba(0,0,0,0.14), 0 4px 12px rgba(0,0,0,0.06);

        --r-xs: 6px;
        --r-sm: 10px;
        --r-md: 14px;
        --r-xl: 20px;
        --r-xl2: 26px;
        --r-pill: 999px;

        --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
        --ease-ios:    cubic-bezier(0.25, 0.1, 0.25, 1);
        --dur-fast:    0.18s;
        --dur-mid:     0.28s;
        --dur-slow:    0.42s;
    }

    @media (prefers-color-scheme: dark) {
        :root {
            --c-bg:           #121212;
            --c-surface:      #1e1e2e;
            --c-surface2:     #161622;
            --c-fill:         rgba(255,255,255,0.08);
            --c-fill2:        rgba(255,255,255,0.12);
            --c-separator:    rgba(255,255,255,0.15);
            --c-separator2:   rgba(255,255,255,0.08);
            --t-primary:   #f5f5f7;
            --t-secondary: #a1a1aa;
            --t-tertiary:  #71717a;
            --t-label:     #52525b;
            --primary:        #0a84ff;
        }
    }

    /* 3-Pane Layout System */
    .app-workspace {
        display: flex;
        height: calc(100vh - 55px);
        background: var(--c-bg);
        border-radius: var(--r-xl);
        overflow: hidden;
        border: 0.5px solid var(--c-separator);
        position: relative;
        font-family: var(--f-system);
        color: var(--t-primary);
    }

    /* Left Pane: Route List */
    .pane-left {
        width: 360px;
        background: var(--c-surface);
        border-right: 0.5px solid var(--c-separator);
        display: flex;
        flex-direction: column;
        z-index: 10;
        flex-shrink: 0;
    }

    .route-item {
        padding: 16px;
        border-bottom: 0.5px solid var(--c-separator2);
        cursor: pointer;
        user-select: none;
        transition: background var(--dur-fast), transform var(--dur-fast);
        display: flex;
        flex-direction: column;
        background: var(--c-surface);
        border: 0.5px solid var(--c-separator);
        border-radius: var(--r-md);
        margin: 12px 12px 0 12px;
        box-shadow: var(--shadow-xs);
    }
    .route-item:hover {
        background: var(--c-surface2);
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }
    .route-item.active {
        background: var(--c-surface);
        border-color: var(--c-blue);
        box-shadow: 0 0 0 1.5px var(--c-blue), var(--shadow-md);
    }
    
    .r-title {
        font-weight: 700;
        font-size: 15px;
        color: var(--t-primary);
        margin-bottom: 4px;
        line-height: 1.25;
    }
    .r-sub {
        font-size: 11px;
        color: var(--t-secondary);
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 6px;
        letter-spacing: 0.04em;
    }
    .r-meta {
        font-size: 12.5px;
        color: var(--t-secondary);
        display: flex;
        justify-content: space-between;
    }
    
    .status-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 6px;
    }
    .status-Active { background: var(--c-orange); }
    .status-Completed { background: var(--c-green); }

    /* Middle Pane: Workspace */
    .pane-middle {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: var(--c-surface2);
        position: relative;
        min-width: 0;
    }
    .mid-header {
        padding: 12px 20px;
        border-bottom: 0.5px solid var(--c-separator);
        background: var(--c-surface);
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        background: var(--c-surface);
        border-radius: var(--r-md);
        overflow: hidden;
        border: 0.5px solid var(--c-separator);
    }
    .data-table th {
        padding: 8px 12px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--t-label);
        background: var(--c-surface2);
        border-bottom: 0.5px solid var(--c-separator);
        white-space: nowrap;
        text-align: left;
    }
    .data-table td {
        padding: 8px 12px;
        font-size: 13.5px;
        color: var(--t-primary);
        border-bottom: 0.5px solid var(--c-separator2);
        vertical-align: middle;
    }
    .data-table tbody tr:last-child td {
        border-bottom: none;
    }
    .data-table tbody tr {
        transition: background var(--dur-fast);
    }
    .data-table tbody tr:hover {
        background: var(--c-fill);
    }
    
    .bill-row { cursor: pointer; transition: 0.1s; user-select: none; }
    .bill-row:hover { background: var(--c-fill2); }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--t-tertiary);
    }
    .empty-state span { font-size: 48px; margin-bottom: 12px; opacity: 0.4; }

    /* Right Pane: Slide-out Invoice Mini-View */
    .pane-right-slider { 
        position: absolute; top: 0; right: 0; bottom: 0; width: 50%; 
        background: var(--c-surface); border-left: 0.5px solid var(--c-separator); 
        box-shadow: var(--shadow-xl); transform: translateX(100%); 
        transition: transform var(--dur-mid) var(--ease-ios); z-index: 150;
        display: flex; flex-direction: column;
    }
    .pane-right-slider.open { transform: translateX(0); }
    
    .slider-header {
        padding: 16px 20px;
        background: var(--c-surface2);
        border-bottom: 0.5px solid var(--c-separator);
        color: var(--t-primary);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 700;
        font-size: 15px;
    }
    .slider-header button {
        cursor: pointer;
    }
    
    #invoiceIframe { width: 100%; flex: 1; border: none; background: #525659; }

    /* Highlights & Stats */
    .stat-box {
        background: var(--c-surface2);
        border: 0.5px solid var(--c-separator);
        padding: 10px 16px;
        border-radius: var(--r-md);
        text-align: center;
        box-shadow: var(--shadow-xs);
    }
    .stat-box span {
        display: block;
        font-size: 10px;
        color: var(--t-label);
        text-transform: uppercase;
        font-weight: 700;
        margin-bottom: 4px;
        letter-spacing: 0.04em;
    }
    .stat-box strong {
        font-size: 18px;
        color: var(--t-primary);
        font-weight: 700;
    }

    /* Modal styles */
    .modal-backdrop {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }
    .modal-panel {
        background: var(--c-surface);
        width: 100%;
        max-width: 480px;
        border-radius: var(--r-xl);
        box-shadow: var(--shadow-xl);
        border: 0.5px solid var(--c-separator);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .modal-header {
        padding: 16px 20px;
        background: var(--c-surface2) !important;
        border-bottom: 0.5px solid var(--c-separator) !important;
        color: var(--t-primary) !important;
        font-weight: 700;
        font-size: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-header button {
        background: var(--c-fill) !important;
        border: none !important;
        color: var(--t-secondary) !important;
        width: 26px !important;
        height: 26px !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 11px !important;
        cursor: pointer !important;
        font-weight: 700 !important;
        transition: background var(--dur-fast), color var(--dur-fast) !important;
    }
    .modal-header button:hover {
        background: var(--c-fill2) !important;
        color: var(--t-primary) !important;
    }
    .modal-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .modal-body label {
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        color: var(--t-label);
        margin-bottom: 6px;
        display: block;
        letter-spacing: 0.04em;
    }
    .modal-body input, .modal-body select, .modal-body textarea {
        width: 100%;
        padding: 8px 12px;
        border: 0.5px solid var(--c-separator);
        border-radius: var(--r-xs);
        box-sizing: border-box;
        font-size: 13.5px;
        background: var(--c-surface);
        color: var(--t-primary);
        outline: none;
        transition: border-color var(--dur-fast);
    }
    .modal-body input:focus, .modal-body select:focus, .modal-body textarea:focus {
        border-color: var(--c-blue);
    }
    .modal-footer {
        padding: 16px 20px;
        background: var(--c-surface2);
        border-top: 0.5px solid var(--c-separator);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    /* GPS path map overlay */
    .map-empty-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.8);
        color: var(--t-secondary);
        font-size: 13.5px;
        font-weight: 600;
        text-align: center;
        padding: 20px;
        z-index: 500;
        pointer-events: none;
    }
    @media (prefers-color-scheme: dark) {
        .map-empty-overlay { background: rgba(30,30,46,0.85); color: var(--t-secondary); }
    }
    
    .path-step-list {
        max-height: 120px;
        overflow-y: auto;
        padding: 12px 20px;
        font-size: 11px;
        color: var(--t-secondary);
        background: var(--c-surface);
        border-top: 0.5px solid var(--c-separator);
        flex-shrink: 0;
    }
    .path-step-list ol { margin: 0; padding-left: 18px; }
    .path-step-list li { margin-bottom: 2px; }
    .path-step-start { color: var(--c-green); font-weight: 700; }
    .path-step-invoice { color: var(--c-blue); }
    .path-step-end { color: var(--c-red); font-weight: 700; }

    /* Left side Tabs styling */
    .left-tab-btn {
        flex: 1;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 600;
        border-radius: var(--r-sm);
        border: 0.5px solid var(--c-separator);
        background: var(--c-surface);
        color: var(--t-secondary);
        cursor: pointer;
        white-space: nowrap;
        transition: all var(--dur-fast);
    }
    .left-tab-btn.active {
        background: var(--c-blue);
        color: white;
        border-color: var(--c-blue);
        box-shadow: var(--shadow-xs);
    }

    /* Binding panel */
    .rb-slot-column {
        background: var(--c-surface2);
        border: 0.5px solid var(--c-separator);
        border-radius: var(--r-md);
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        box-shadow: var(--shadow-xs);
    }
    .rb-slot-box {
        border: 1.5px dashed var(--c-separator);
        border-radius: var(--r-sm);
        padding: 20px;
        text-align: center;
        background: var(--c-surface);
        cursor: pointer;
        transition: all var(--dur-fast) var(--ease-ios);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 80px;
    }
    .rb-slot-box:hover {
        border-color: var(--c-blue);
        background: var(--c-blue-light);
    }
    .rb-slot-select {
        width: 100%;
        padding: 8px 12px;
        border: 0.5px solid var(--c-separator);
        border-radius: var(--r-xs);
        font-size: 13px;
        background: var(--c-surface);
        color: var(--t-primary);
        margin-top: 5px;
    }
    .rb-bill-list {
        max-height: 180px;
        overflow-y: auto;
        border: 0.5px solid var(--c-separator);
        border-radius: var(--r-sm);
        background: var(--c-surface);
        padding: 8px;
        font-size: 12px;
        display: none;
    }
    .rb-bill-item {
        display: flex;
        justify-content: space-between;
        padding: 6px 8px;
        border-bottom: 0.5px solid var(--c-separator2);
        align-items: center;
    }
    .rb-bill-item:last-child {
        border-bottom: none;
    }
    .rb-bound-tag {
        margin-top: 6px;
        font-size: 11px;
        background: var(--c-blue-light);
        color: var(--c-blue);
        padding: 3px 8px;
        border-radius: var(--r-xs);
        display: inline-block;
        font-weight: 700;
        border: 0.5px solid var(--c-blue-mid);
    }

    /* Scrollable Stage Tab styling (deprecated/hidden for sidebar, kept for JS compatibility) */
    .scroll-tabs {
        display: none !important;
    }
    
    .global-filter-btn {
        padding: 6px 14px;
        background: var(--c-surface2);
        border: 0.5px solid var(--c-separator);
        border-radius: var(--r-pill);
        color: var(--t-secondary);
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all var(--dur-fast);
        white-space: nowrap;
    }
    .global-filter-btn:hover {
        background: var(--c-fill);
        color: var(--t-primary);
    }
    .global-filter-btn.active {
        background: var(--c-blue-light);
        color: var(--c-blue);
        border-color: var(--c-blue-mid);
        box-shadow: var(--shadow-xs);
    }

    /* Premium Workflow Sidebar Styling */
    .workflow-sidebar {
        width: 260px;
        background: var(--c-surface);
        border-right: 0.5px solid var(--c-separator);
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        padding: 16px 8px;
        flex-shrink: 0;
    }
    
    .workflow-sidebar-steps {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .workflow-sidebar-steps::before {
        content: '';
        position: absolute;
        left: 25px; /* Align with center of 26px step-dot */
        top: 20px;
        bottom: 20px;
        width: 1.5px;
        background: var(--c-separator);
        z-index: 0;
    }
    .sidebar-step-item {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 12px;
        border-radius: var(--r-md);
        cursor: pointer;
        user-select: none;
        transition: background var(--dur-fast), transform var(--dur-fast);
        border: 0.5px solid transparent;
        background: transparent;
    }
    .sidebar-step-item:hover {
        background: var(--c-fill);
    }
    .sidebar-step-item.active {
        background: var(--c-blue-light);
        border-color: var(--c-blue-mid);
    }
    .sidebar-step-item.locked {
        opacity: 1;
    }
    
    .step-dot {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        flex-shrink: 0;
        background: var(--c-surface);
        color: var(--t-secondary);
        transition: background var(--dur-fast), color var(--dur-fast), box-shadow var(--dur-fast);
        border: 1px solid var(--c-separator);
        z-index: 2;
    }
    .sidebar-step-item.active .step-dot {
        background: var(--c-blue);
        color: #fff;
        border-color: var(--c-blue);
        box-shadow: 0 0 0 3.5px rgba(0, 122, 255, 0.15);
    }
    .sidebar-step-item.completed .step-dot {
        background: var(--c-green-light);
        color: var(--c-green);
        border-color: var(--c-green);
    }
    .sidebar-step-item.locked .step-dot {
        background: var(--c-surface);
        color: var(--t-secondary);
        border-color: var(--c-separator);
    }

    .step-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
        flex: 1;
        min-width: 0;
    }
    .step-title {
        font-size: 13.5px;
        font-weight: 600;
        color: var(--t-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sidebar-step-item.active .step-title {
        color: var(--c-blue);
        font-weight: 700;
    }
    .sidebar-step-item.completed .step-title {
        color: var(--t-primary);
    }
    .step-desc {
        font-size: 11px;
        color: var(--t-label);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sidebar-step-item.active .step-desc {
        color: rgba(0, 122, 255, 0.85);
    }

    /* Modern Dashboard Navigation flow toggles */
    .app-workspace.workspace-active .pane-left {
        display: none !important;
    }
    .app-workspace.workspace-active .pane-middle {
        display: flex !important;
        flex: 1;
        width: 100%;
    }
    .app-workspace:not(.workspace-active) .pane-left {
        width: 100% !important;
        background: transparent !important;
        border-right: none !important;
        flex: 1;
        display: flex !important;
    }
    .app-workspace:not(.workspace-active) #routeListItemsContainer {
        display: grid !important;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)) !important;
        gap: 20px !important;
        padding: 20px !important;
        align-content: start !important;
    }
    .global-filter-scroll {
        scrollbar-width: none; /* Firefox */
    }
    .global-filter-scroll::-webkit-scrollbar {
        display: none; /* Safari and Chrome */
    }
    
    .app-workspace:not(.workspace-active) .route-item {
        background: var(--c-surface) !important;
        border: 0.5px solid var(--c-separator) !important;
        border-radius: var(--r-md) !important;
        box-shadow: var(--shadow-sm) !important;
        display: flex !important;
        flex-direction: column !important;
        transition: all var(--dur-fast) var(--ease-ios) !important;
        padding: 18px !important;
        margin: 0 !important;
    }
    .app-workspace:not(.workspace-active) .route-item:hover {
        transform: translateY(-2px) !important;
        box-shadow: var(--shadow-md) !important;
        border-color: var(--c-blue) !important;
        background: var(--c-surface) !important;
    }
    .app-workspace:not(.workspace-active) .route-item.active {
        background: var(--c-surface) !important;
        color: inherit !important;
        border-color: var(--c-blue) !important;
        box-shadow: 0 0 0 1.5px var(--c-blue), var(--shadow-md) !important;
    }
    .app-workspace:not(.workspace-active) .pane-middle {
        display: none !important;
    }
    body.workspace-showing .header-actions,
    body.workspace-showing .global-status-filter-bar {
        display: none !important;
    }

    @media (prefers-color-scheme: dark) {
        .app-workspace:not(.workspace-active) .route-item {
            background: var(--c-surface) !important;
            border-color: var(--c-separator) !important;
        }
        .app-workspace:not(.workspace-active) .route-item:hover {
            background: var(--c-surface2) !important;
        }
    }

    /* Premium Button Stylings inside Workspace */
    .workspace-tab-panel button, 
    .mid-header button, 
    .header-actions button,
    #boundRouteSummaryContainer button,
    #completedArchiveBanner button {
        padding: 8px 16px !important;
        border-radius: var(--r-md) !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        border: 0.5px solid transparent !important;
        cursor: pointer !important;
        transition: transform var(--dur-fast) var(--ease-spring), filter var(--dur-fast), background var(--dur-fast) !important;
        box-shadow: var(--shadow-xs) !important;
    }
    
    .workspace-tab-panel button:active, 
    .mid-header button:active, 
    .header-actions button:active,
    #boundRouteSummaryContainer button:active,
    #completedArchiveBanner button:active {
        transform: scale(0.97) !important;
    }
    
    /* Auto-assign theme colors based on original background colors */
    button[style*="#2e7d32"], button[style*="background:#2e7d32"], button[style*="background: #2e7d32"] {
        background: var(--c-green) !important;
        color: #fff !important;
    }
    button[style*="#3f51b5"], button[style*="background:#3f51b5"], button[style*="background: #3f51b5"],
    button[style*="#0066cc"], button[style*="background:#0066cc"], button[style*="background: #0066cc"] {
        background: var(--c-blue) !important;
        color: #fff !important;
    }
    button[style*="#ef4444"], button[style*="background:#ef4444"], button[style*="background: #ef4444"],
    button[style*="#c62828"], button[style*="background:#c62828"], button[style*="background: #c62828"] {
        background: var(--c-red) !important;
        color: #fff !important;
    }
    button[style*="#e2e8f0"], button[style*="background:#e2e8f0"], button[style*="background: #e2e8f0"] {
        background: var(--c-surface) !important;
        color: var(--t-primary) !important;
        border-color: var(--c-separator) !important;
    }
    button[style*="#e2e8f0"]:hover, button[style*="background:#e2e8f0"]:hover, button[style*="background: #e2e8f0"]:hover {
        background: var(--c-surface2) !important;
    }

    /* Input elements style inside workspace tabs */
    .workspace-tab-panel input, 
    .workspace-tab-panel select, 
    .workspace-tab-panel textarea {
        padding: 8px 12px;
        border: 0.5px solid var(--c-separator);
        border-radius: var(--r-xs);
        font-size: 13.5px;
        background: var(--c-surface);
        color: var(--t-primary);
        outline: none;
        transition: border-color var(--dur-fast);
    }
    .workspace-tab-panel input:focus, 
    .workspace-tab-panel select:focus, 
    .workspace-tab-panel textarea:focus {
        border-color: var(--c-blue);
    }

    /* Header styling customization */
    .header-actions h2 i {
        color: var(--c-blue) !important;
    }

    /* ---- Command Bar (Dynamic Island style) ---- */
    .cmd-bar {
        position: fixed;
        bottom: 28px; left: 50%;
        transform: translateX(-50%);
        background: rgba(28, 28, 30, 0.92);
        backdrop-filter: saturate(180%) blur(28px);
        -webkit-backdrop-filter: saturate(180%) blur(28px);
        border: 0.5px solid rgba(255,255,255,0.12);
        border-radius: var(--r-pill);
        padding: 7px 10px;
        display: flex; align-items: center; gap: 4px;
        box-shadow: var(--shadow-xl), 0 0 0 0.5px rgba(0,0,0,0.3);
        z-index: 1000;
    }
    .cmd-search {
        display: flex;
        align-items: center;
        gap: 9px;
        background: rgba(255,255,255,0.1);
        border-radius: var(--r-pill);
        padding: 8px 14px;
        width: 196px;
        transition: width var(--dur-slow) var(--ease-ios),
                    background var(--dur-mid);
    }
    .cmd-search:focus-within {
        width: 300px;
        background: rgba(255,255,255,0.18);
    }
    .cmd-search i { color: rgba(255,255,255,0.55); font-size: 14px; flex-shrink: 0; }
    .cmd-search input {
        background: transparent; border: none; outline: none;
        color: #fff; font-size: 14px; font-weight: 500;
        font-family: var(--f-system); width: 100%;
    }
    .cmd-search input::placeholder { color: rgba(255,255,255,0.45); }
    .cmd-divider { width: 0.5px; height: 22px; background: rgba(255,255,255,0.15); margin: 0 3px; }
    .cmd-icon {
        width: 38px; height: 38px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: rgba(255,255,255,0.8); font-size: 15px;
        background: transparent; border: none; cursor: pointer; text-decoration: none;
        transition: background var(--dur-fast);
    }
    .cmd-icon:hover { background: rgba(255,255,255,0.12); color: #fff; }
    .cmd-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,0.08);
        border-radius: var(--r-pill);
        padding: 6px 12px;
        color: rgba(255,255,255,0.85);
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: background var(--dur-fast), color var(--dur-fast);
        height: 38px;
    }
    .cmd-btn:hover {
        background: rgba(255,255,255,0.18);
        color: #fff;
    }
    .cmd-btn i {
        font-size: 14px;
    }
    body.workspace-showing .cmd-bar {
        display: none !important;
    }

    /* macOS Style UI Elements */
    .macos-window {
        background: #ffffff;
        border: 1px solid #d1d1d6;
        border-radius: 12px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.02);
        max-width: 680px;
        margin: 20px auto;
        overflow: hidden;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    .macos-titlebar {
        background: #f5f5f7;
        border-bottom: 1px solid #d1d1d6;
        padding: 10px 16px;
        display: flex;
        align-items: center;
        position: relative;
    }
    .macos-dots {
        display: flex;
        gap: 8px;
    }
    .macos-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }
    .macos-dot.close { background: #ff5f56; border: 0.5px solid #e0443e; }
    .macos-dot.minimize { background: #ffbd2e; border: 0.5px solid #dfa123; }
    .macos-dot.zoom { background: #27c93f; border: 0.5px solid #1aab29; }
    
    .macos-title {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        font-size: 13px;
        font-weight: 600;
        color: #1d1d1f;
    }
    .macos-content {
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .macos-label {
        font-size: 12px;
        font-weight: 600;
        color: #86868b;
        margin-bottom: 6px;
        display: block;
    }
    .macos-input, .macos-select {
        width: 100%;
        background: #ffffff;
        border: 1px solid #d1d1d6;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 13px;
        color: #1d1d1f;
        outline: none;
        transition: box-shadow 0.15s, border-color 0.15s;
    }
    .macos-input:focus, .macos-select:focus {
        border-color: #007aff;
        box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.25);
    }
    .macos-btn-primary {
        background: #007aff;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .macos-btn-primary:hover {
        background: #0066d6;
    }
    .macos-btn-primary:active {
        background: #0051a8;
    }
    .macos-btn-secondary {
        background: #f2f2f7;
        color: #007aff;
        border: 1px solid #d1d1d6;
        border-radius: 6px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .macos-btn-secondary:hover {
        background: #e5e5ea;
    }
    .macos-btn-secondary:active {
        background: #d1d1d6;
    }
    .macos-checkbox-list {
        border: 1px solid #d1d1d6;
        border-radius: 6px;
        max-height: 180px;
        overflow-y: auto;
        background: #ffffff;
        padding: 4px;
    }
    .macos-checkbox-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 8px 10px;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.12s, color 0.12s;
    }
    .macos-checkbox-item:hover {
        background: rgba(0, 122, 255, 0.08);
    }
    .macos-banner {
        background: rgba(0, 122, 255, 0.05);
        border: 1px solid rgba(0, 122, 255, 0.2);
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 13px;
        color: #007aff;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        margin-bottom: 20px;
    }

    /* Pagination Styles */
    .pag-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed !important;
        color: var(--t-tertiary) !important;
        border-color: var(--c-separator2) !important;
    }
    .pag-btn:not(:disabled):hover {
        background: var(--c-fill) !important;
        border-color: var(--t-tertiary) !important;
    }

    /* iOS iMessage-style Toast Notifications */
    .ios-toast {
        position: fixed;
        top: 24px;
        left: 50%;
        transform: translate(-50%, -20px);
        opacity: 0;
        z-index: 999999;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 10px 16px;
        min-width: 320px;
        max-width: 550px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px) saturate(190%);
        -webkit-backdrop-filter: blur(20px) saturate(190%);
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.02);
        color: #1e293b;
        font-family: var(--f-system);
        font-size: 13.5px;
        font-weight: 500;
        transition: opacity 0.4s var(--ease-ios), transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        animation: iosToastSlideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    
    @keyframes iosToastSlideIn {
        from {
            transform: translate(-50%, -20px) scale(0.95);
            opacity: 0;
        }
        to {
            transform: translate(-50%, 0) scale(1);
            opacity: 1;
        }
    }
    
    .ios-toast.success {
        border-left: 4px solid var(--c-green);
    }
    
    .ios-toast.error {
        border-left: 4px solid var(--c-red);
    }
    
    .ios-toast-content {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
    }
    
    .ios-toast-icon {
        font-size: 16px;
        flex-shrink: 0;
    }
    
    .ios-toast.success .ios-toast-icon {
        color: var(--c-green);
    }
    
    .ios-toast.error .ios-toast-icon {
        color: var(--c-red);
    }
    
    .ios-toast-message {
        line-height: 1.4;
        color: var(--t-primary);
    }
    
    .ios-toast-close {
        background: none;
        border: none;
        color: var(--t-secondary);
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        transition: background 0.2s, color 0.2s;
        flex-shrink: 0;
    }
    
    .ios-toast-close:hover {
        background: var(--c-fill);
        color: var(--t-primary);
    }

    @media (prefers-color-scheme: dark) {
        .ios-toast {
            background: rgba(28, 28, 30, 0.85);
            border-color: rgba(255, 255, 255, 0.1);
            color: #f5f5f7;
        }
    }
</style>
