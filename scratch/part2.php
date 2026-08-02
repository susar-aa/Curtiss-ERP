<!-- ============================================================
     FLOATING COMMAND / SEARCH BAR (DYNAMIC ISLAND)
     ============================================================ -->
<div class="cmd-bar">
    <div class="cmd-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Search suppliers..." onkeyup="filterList()">
    </div>
    <div class="cmd-divider"></div>
    <button type="button" class="cmd-cta" onclick="openModal('addSupplierModal')"><i class="fa-solid fa-plus" style="font-size:13px;"></i> New</button>
</div>

<!-- ============================================================
     POPUP MODAL: SUPPLIER PROFILE LEDGER & DETAILS
     ============================================================ -->
<div id="supplierProfileModal" class="modal-veil hidden" onclick="if(event.target === this) closeSupplierProfile()">
    <div class="sf-modal" style="width: 85%; max-width: 1000px; height: 85vh; display: flex; flex-direction: column; padding: 0; overflow: hidden; border-radius: var(--r-lg);">
        
        <div class="modal-head" id="modal-header-container" style="padding: 18px 24px; border-bottom: 0.5px solid var(--c-separator); background: var(--c-surface2);">
            <div style="display: flex; gap: 15px; align-items: center;">
                <div class="avatar-circle" style="width: 40px; height: 40px; font-size: 16px;">S</div>
                <div>
                    <h3 class="modal-title" style="font-size: 18px; font-weight: 700;">Supplier Details</h3>
                </div>
            </div>
            <button type="button" onclick="closeSupplierProfile()" class="modal-close" style="width:30px; height:30px;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <div id="modal-loader" style="display:none; flex:1; align-items:center; justify-content:center; flex-direction:column; gap:12px; background:var(--c-surface);">
            <i class="fa-solid fa-spinner spin" style="font-size:32px; color:var(--c-blue);"></i>
            <span style="font-size:14px; color:var(--t-secondary); font-weight:500;">Loading supplier profile...</span>
        </div>
        
        <div id="modal-profile-content" style="flex:1; display:flex; flex-direction:column; overflow:hidden;">
            <!-- Content will load dynamically here -->
        </div>
    </div>
</div>

<!-- ============================================================
     HIDDEN TEMPLATE SOURCES (EXTRACTED VIA DOM PARSER)
     ============================================================ -->
<?php if ($data['selected_supplier']): ?>
    <?php $sup = $data['selected_supplier']; $stats = $data['stats']; ?>
    
    <div id="modal-header-source" class="hidden">
        <div style="display: flex; gap: 15px; align-items: center; min-width: 0; flex: 1;">
            <div class="avatar-circle" style="width: 40px; height: 40px; font-size: 16px; background: var(--c-blue-light); color: var(--c-blue); flex-shrink: 0;">
                <?= strtoupper(substr($sup->name ?? '', 0, 2)) ?>
            </div>
            <div style="min-width: 0; max-width: 250px;">
                <h3 class="modal-title" style="font-size: 16.5px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0;" title="<?= htmlspecialchars($sup->name ?? '') ?>">
                    <?= htmlspecialchars($sup->name ?? '') ?>
                </h3>
                <div style="font-size: 11px; color: var(--t-secondary); display: flex; gap: 10px; margin-top: 2px; white-space: nowrap; overflow: hidden; align-items: center;">
                    <span>📞 <?= !empty($sup->phone) ? htmlspecialchars($sup->phone) : '<span style="color:var(--c-red); font-weight:600;">Missing</span>' ?></span>
                    <span>✉️ <?= !empty($sup->email) ? htmlspecialchars($sup->email) : '<span style="color:var(--c-red); font-weight:600;">Missing</span>' ?></span>
                </div>
            </div>
        </div>
        
        <!-- Header Statistics Cards -->
        <div style="display: flex; gap: 8px; margin-right: 18px; flex-shrink: 0; align-items: center;">
            <div style="background: var(--c-fill); padding: 5px 10px; border-radius: var(--r-sm); text-align: center; min-width: 100px;">
                <div style="font-size: 8.5px; color: var(--t-label); text-transform: uppercase; font-weight: 700; letter-spacing: 0.04em;">Total Billed</div>
                <div style="font-size: 13px; font-weight: bold; color: var(--t-primary); margin-top: 1px; font-family: var(--f-mono);">Rs: <?= number_format($stats->total_billed, 2) ?></div>
            </div>
            <div style="background: var(--c-green-light); padding: 5px 10px; border-radius: var(--r-sm); border: 0.5px solid rgba(52,199,89,0.2); text-align: center; min-width: 100px;">
                <div style="font-size: 8.5px; color: var(--c-green); text-transform: uppercase; font-weight: 700; letter-spacing: 0.04em;">Total Paid</div>
                <div style="font-size: 13px; font-weight: bold; color: var(--c-green); margin-top: 1px; font-family: var(--f-mono);">Rs: <?= number_format($stats->total_paid, 2) ?></div>
            </div>
            <div style="background: <?= $stats->outstanding > 0 ? 'var(--c-red-light)' : 'var(--c-green-light)' ?>; padding: 5px 10px; border-radius: var(--r-sm); border: 0.5px solid <?= $stats->outstanding > 0 ? 'rgba(255,59,48,0.2)' : 'rgba(52,199,89,0.2)' ?>; text-align: center; min-width: 100px;">
                <div style="font-size: 8.5px; color: <?= $stats->outstanding > 0 ? 'var(--c-red)' : 'var(--c-green)' ?>; text-transform: uppercase; font-weight: 700; letter-spacing: 0.04em;">Outstanding</div>
                <div style="font-size: 13px; font-weight: bold; color: <?= $stats->outstanding > 0 ? 'var(--c-red)' : 'var(--c-green)' ?>; margin-top: 1px; font-family: var(--f-mono);">Rs: <?= number_format($stats->outstanding, 2) ?></div>
            </div>
        </div>

        <button type="button" onclick="closeSupplierProfile()" class="modal-close" style="width:30px; height:30px; flex-shrink: 0;"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <div id="modal-profile-content-source" class="hidden">
        <!-- Tab Navigation -->
        <div class="tabs">
            <button class="tab-btn active" onclick="switchModalTab('ledger')" id="mbtn_ledger">Activity Ledger</button>
            <button class="tab-btn" onclick="switchModalTab('pos')" id="mbtn_pos">Purchase Orders</button>
            <button class="tab-btn" onclick="switchModalTab('products')" id="mbtn_products">Products List</button>
            <button class="tab-btn" onclick="switchModalTab('profile')" id="mbtn_profile">Profile</button>
        </div>

        <!-- TAB 1: Ledger -->
        <div id="mtab_ledger" class="tab-content active" style="padding: 22px; overflow-y: auto; flex: 1;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Ref / Desc</th>
                        <th class="num-col">Dr (Paid/Ret)</th>
                        <th class="num-col">Cr (Billed)</th>
                        <th class="num-col">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['ledger'])): ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--t-secondary); padding: 20px;">No financial activity recorded.</td></tr>
                    <?php else: foreach($data['ledger'] as $l): ?>
                        <tr>
                            <td style="color:var(--t-secondary); font-size:12px;"><?= date('M d, Y', strtotime($l->date)) ?></td>
                            <td><strong><?= $l->type ?></strong></td>
                            <td>
                                <?php if($l->type == 'GRN'): ?>
                                    <a href="<?= APP_URL ?>/grn/show/<?= $l->id ?>" target="_blank" style="color:var(--c-blue); font-weight:bold; text-decoration:none;">
                                        <?= htmlspecialchars($l->ref) ?> ↗
                                    </a>
                                <?php elseif($l->type == 'Supplier Return'): ?>
                                    <a href="<?= APP_URL ?>/supplier-return/show/<?= $l->id ?>" target="_blank" style="color:var(--c-red); font-weight:bold; text-decoration:none;">
                                        <?= htmlspecialchars($l->ref) ?> ↗
                                    </a>
                                <?php else: ?>
                                    <span style="color:var(--t-primary);"><?= htmlspecialchars($l->ref) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="num-col" style="color:var(--c-green); font-weight:500; font-family:var(--f-mono);"><?= $l->debit > 0 ? 'Rs: ' . number_format($l->debit, 2) : '-' ?></td>
                            <td class="num-col" style="font-weight:500; font-family:var(--f-mono);"><?= $l->credit > 0 ? 'Rs: ' . number_format($l->credit, 2) : '-' ?></td>
                            <td class="num-col" style="font-weight:bold; font-family:var(--f-mono); color: <?= $l->balance > 0 ? 'var(--c-red)' : 'var(--c-green)' ?>;">Rs: <?= number_format($l->balance, 2) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- TAB 2: Purchase Orders -->
        <div id="mtab_pos" class="tab-content" style="padding: 22px; overflow-y: auto; flex: 1;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Expected</th>
                        <th>PO Number</th>
                        <th>Status</th>
                        <th class="num-col">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['pos'])): ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--t-secondary); padding: 20px;">No purchase orders.</td></tr>
                    <?php else: foreach($data['pos'] as $po): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($po->po_date)) ?></td>
                            <td style="color:var(--t-secondary);"><?= $po->expected_date ? date('M d, Y', strtotime($po->expected_date)) : 'N/A' ?></td>
                            <td><strong><?= htmlspecialchars($po->po_number) ?></strong></td>
                            <td><span class="sf-badge badge-<?= $po->status ?>"><?= $po->status ?></span></td>
                            <td class="num-col" style="font-weight:700; font-family:var(--f-mono);">Rs. <?= number_format($po->total_amount, 2) ?></td>
                            <td style="text-align:right;">
                                <a href="<?= APP_URL ?>/purchase/show/<?= $po->id ?>" target="_blank" class="sf-btn neutral sf-btn-small">View</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- TAB 3: Products -->
        <div id="mtab_products" class="tab-content" style="padding: 22px; overflow-y: auto; flex: 1;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Product Name</th>
                        <th class="num-col">Last Cost</th>
                        <th class="num-col">Sell Price</th>
                        <th class="num-col">Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['products'])): ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--t-secondary); padding: 20px;">No products assigned.</td></tr>
                    <?php else: foreach($data['products'] as $p): ?>
                        <tr>
                            <td style="color:var(--t-secondary); font-family:var(--f-mono); font-size:13px;"><?= htmlspecialchars($p->sku ?: 'N/A') ?></td>
                            <td><strong><?= htmlspecialchars($p->product_name) ?></strong></td>
                            <td class="num-col" style="font-weight:600; font-family:var(--f-mono);">Rs. <?= number_format($p->cost_price, 2) ?></td>
                            <td class="num-col" style="color:var(--c-blue); font-weight:600; font-family:var(--f-mono);">Rs. <?= number_format($p->price, 2) ?></td>
                            <td class="num-col" style="font-weight:700; font-family:var(--f-mono); color: <?= $p->quantity_on_hand > 5 ? 'var(--t-primary)' : 'var(--c-orange)' ?>;">
                                <?= number_format($p->quantity_on_hand) ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- TAB 4: Profile -->
        <div id="mtab_profile" class="tab-content" style="padding: 22px; overflow-y: auto; flex: 1;">
            <h4 style="margin:0 0 14px 0; border-bottom: 0.5px solid var(--c-separator); padding-bottom: 6px; font-size:14px; font-weight:700; text-transform:uppercase; color:var(--t-secondary);">
                Edit Supplier Profile
            </h4>
            
            <form action="<?= APP_URL ?>/supplier/index/<?= $sup->id ?>" method="POST" style="max-width: 600px;">
                <input type="hidden" name="action" value="update_supplier">
                <input type="hidden" name="supplier_id" value="<?= $sup->id ?>">
                
                <div class="grid-2">
                    <div class="sf-group">
                        <label>Company Name *</label>
                        <input type="text" name="name" class="sf-input" value="<?= htmlspecialchars($sup->name) ?>" required>
                    </div>
                    <div class="sf-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="sf-input" value="<?= htmlspecialchars($sup->email) ?>">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="sf-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="sf-input" value="<?= htmlspecialchars($sup->phone) ?>">
                    </div>
                    <div class="sf-group">
                        <label>Physical Address</label>
                        <textarea name="address" class="sf-input" rows="2" style="resize:vertical; min-height:50px;"><?= htmlspecialchars($sup->address) ?></textarea>
                    </div>
                </div>
                
                <div style="text-align: right; margin-top: 10px;">
                    <button type="submit" class="sf-btn primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- ============================================================
     ADD SUPPLIER MODAL
     ============================================================ -->
<div class="modal-veil hidden" id="addSupplierModal" onclick="if(event.target === this) closeModal('addSupplierModal')">
    <div class="sf-modal" style="width: 480px;">
        <div class="modal-head">
            <h3 class="modal-title">Add New Supplier</h3>
            <button type="button" class="modal-close" onclick="closeModal('addSupplierModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form action="<?= APP_URL ?>/supplier" method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="add_supplier">
                <div class="sf-group">
                    <label>Company Name *</label>
                    <input type="text" name="name" class="sf-input" required>
                </div>
                <div class="sf-group">
                    <label>Email</label>
                    <input type="email" name="email" class="sf-input">
                </div>
                <div class="sf-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="sf-input">
                </div>
                <div class="sf-group">
                    <label>Address</label>
                    <textarea name="address" class="sf-input" rows="2" style="resize:vertical;"></textarea>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="sf-btn neutral" onclick="closeModal('addSupplierModal')">Cancel</button>
                <button type="submit" class="sf-btn primary">Register Supplier</button>
            </div>
        </form>
    </div>
</div>

<script>
    // --- Pagination and List Globals ---
    let allSuppliers = [];
    let filteredSuppliers = [];
    let currentPage = 1;
    let pageSize = 15;

    document.addEventListener("DOMContentLoaded", function() {
        const rows = document.querySelectorAll('#supList .supplier-row');
        rows.forEach(row => {
            allSuppliers.push({
                element: row,
                name: row.getAttribute('data-name'),
                phone: row.getAttribute('data-phone'),
                email: row.getAttribute('data-email'),
                outstanding: parseFloat(row.getAttribute('data-outstanding'))
            });
        });
        filteredSuppliers = [...allSuppliers];
        renderPagination();
        
        // Auto-open profile modal if URL has ?supplier_id=... or /supplier/index/123
        const pathParts = window.location.pathname.split('/');
        const idIndex = pathParts.indexOf('index');
        let autoLoadId = null;
        if (idIndex !== -1 && pathParts.length > idIndex + 1) {
            autoLoadId = pathParts[idIndex + 1];
        } else {
            const urlParams = new URLSearchParams(window.location.search);
            autoLoadId = urlParams.get('supplier_id');
        }
        
        if (autoLoadId && !isNaN(autoLoadId)) {
            const tab = new URLSearchParams(window.location.search).get('tab');
            showSupplierProfile(autoLoadId, tab);
        }
    });

    // --- Search & Filter Handlers ---
    let filterStatusValue = '';
    
    function filterList() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        
        filteredSuppliers = allSuppliers.filter(c => {
            const matchQuery = c.name.includes(query) || c.phone.includes(query) || c.email.includes(query);
            let matchStatus = true;
            
            if (filterStatusValue === 'owed') matchStatus = (c.outstanding > 0);
            else if (filterStatusValue === 'cleared') matchStatus = (c.outstanding <= 0);
            
            return matchQuery && matchStatus;
        });
        
        currentPage = 1;
        renderPagination();
        document.getElementById('matching-count').innerText = filteredSuppliers.length;
    }

    function selectStatus(val, label) {
        document.getElementById('filterStatus').value = val;
        document.getElementById('status-dropdown-val').innerText = label;
        filterStatusValue = val;
        filterList();
        document.activeElement.blur();
    }

    function clearAllFilters() {
        document.getElementById('searchInput').value = '';
        selectStatus('', 'All Accounts');
    }

    // --- Pagination Render ---
    function renderPagination() {
        allSuppliers.forEach(c => c.element.style.display = 'none');
        
        if (filteredSuppliers.length === 0) {
            document.getElementById('pg-info-text').innerHTML = "No matching suppliers";
            document.getElementById('pg-current-text').innerText = "0 / 0";
            document.getElementById('pg-prev-btn').disabled = true;
            document.getElementById('pg-next-btn').disabled = true;
            return;
        }
        
        let totalPages = Math.ceil(filteredSuppliers.length / pageSize);
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;
        
        let startIdx = (currentPage - 1) * pageSize;
        let endIdx = startIdx + pageSize;
        
        for (let i = startIdx; i < endIdx && i < filteredSuppliers.length; i++) {
            filteredSuppliers[i].element.style.display = 'table-row';
        }
        
        let showingEnd = Math.min(endIdx, filteredSuppliers.length);
        document.getElementById('pg-info-text').innerHTML = `Showing <strong>${startIdx + 1}</strong> – <strong>${showingEnd}</strong> of <strong>${filteredSuppliers.length}</strong>`;
        document.getElementById('pg-current-text').innerText = `${currentPage} / ${totalPages}`;
        
        document.getElementById('pg-prev-btn').disabled = (currentPage === 1);
        document.getElementById('pg-next-btn').disabled = (currentPage === totalPages);
    }
    
    function navigatePage(page) {
        let totalPages = Math.ceil(filteredSuppliers.length / pageSize);
        if (page >= 1 && page <= totalPages) {
            currentPage = page;
            renderPagination();
        }
    }
    
    function updatePageSize(size) {
        if (size === '1000') { pageSize = 999999; } else { pageSize = parseInt(size); }
        currentPage = 1;
        renderPagination();
    }

    // --- Modal Control Helper functions ---
    function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
    function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

    // --- Supplier Profile Popup Modal Handlers ---
    function showSupplierProfile(id, tab = null) {
        const modal = document.getElementById('supplierProfileModal');
        const loader = document.getElementById('modal-loader');
        const content = document.getElementById('modal-profile-content');
        
        modal.classList.remove('hidden');
        loader.style.display = 'flex';
        content.style.display = 'none';
        
        let targetUrl = '<?= APP_URL ?>/supplier/index/' + id;
        if (tab) { targetUrl += '?tab=' + tab; }
        window.history.pushState({ path: targetUrl }, '', targetUrl);
        
        fetch(targetUrl)
            .then(response => {
                if (!response.ok) throw new Error('Failed to load profile');
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const newContent = doc.getElementById('modal-profile-content-source');
                const newHeader = doc.getElementById('modal-header-source');
                
                if (newContent && newHeader) {
                    content.innerHTML = newContent.innerHTML;
                    document.getElementById('modal-header-container').innerHTML = newHeader.innerHTML;
                    loader.style.display = 'none';
                    content.style.display = 'flex';
                    
                    if (tab) { switchModalTab(tab); } 
                    else { switchModalTab('ledger'); }
                } else {
                    throw new Error('Profile layout mismatch');
                }
            })
            .catch(err => {
                console.error(err);
                loader.style.display = 'none';
                content.innerHTML = `<div style="padding:40px; text-align:center; color:var(--c-red); font-weight:600;"><i class="fa-solid fa-triangle-exclamation" style="font-size:24px; margin-bottom:10px; display:block;"></i>Failed to load supplier profile data.</div>`;
                content.style.display = 'block';
            });
    }

    function closeSupplierProfile() {
        document.getElementById('supplierProfileModal').classList.add('hidden');
        window.history.pushState({ path: '<?= APP_URL ?>/supplier' }, '', '<?= APP_URL ?>/supplier');
    }

    function switchModalTab(tabName) {
        const content = document.getElementById('modal-profile-content');
        content.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
        content.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        
        const tabEl = content.querySelector('#mtab_' + tabName);
        const btnEl = content.querySelector('#mbtn_' + tabName);
        
        if (tabEl) tabEl.style.display = 'block';
        if (btnEl) btnEl.classList.add('active');
        
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('tab', tabName);
        const newUrl = window.location.pathname + '?' + urlParams.toString();
        window.history.replaceState({ path: newUrl }, '', newUrl);
    }
</script>
