
console.log("--- INLINE SCRIPT IS STARTING TO PARSE! ---");
try {
    window.allSuppliers = [];
    window.filteredSuppliers = [];
    window.currentPage = 1;
    window.pageSize = 15;

    document.addEventListener("DOMContentLoaded", function() {
        const rows = document.querySelectorAll('#supList .supplier-row');
        rows.forEach(row => {
            window.allSuppliers.push({
                element: row,
                name: row.getAttribute('data-name'),
                phone: row.getAttribute('data-phone'),
                email: row.getAttribute('data-email'),
                outstanding: parseFloat(row.getAttribute('data-outstanding'))
            });
        });
        window.filteredSuppliers = [...window.allSuppliers];
        window.renderPagination();
        
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
            window.openSupplierModalPopup(autoLoadId, tab);
        }
    });

    window.filterStatusValue = '';
    
    window.filterList = function() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        
        window.filteredSuppliers = window.allSuppliers.filter(c => {
            const matchQuery = c.name.includes(query) || c.phone.includes(query) || c.email.includes(query);
            let matchStatus = true;
            
            if (window.filterStatusValue === 'owed') matchStatus = (c.outstanding > 0);
            else if (window.filterStatusValue === 'cleared') matchStatus = (c.outstanding <= 0);
            
            return matchQuery && matchStatus;
        });
        
        window.currentPage = 1;
        window.renderPagination();
        document.getElementById('matching-count').innerText = window.filteredSuppliers.length;
    }

    window.selectStatus = function(val, label) {
        document.getElementById('filterStatus').value = val;
        document.getElementById('status-dropdown-val').innerText = label;
        window.filterStatusValue = val;
        window.filterList();
        document.activeElement.blur();
    }

    window.clearAllFilters = function() {
        document.getElementById('searchInput').value = '';
        window.selectStatus('', 'All Accounts');
    }

    window.renderPagination = function() {
        window.allSuppliers.forEach(c => c.element.style.display = 'none');
        
        if (window.filteredSuppliers.length === 0) {
            document.getElementById('pg-info-text').innerHTML = "No matching suppliers";
            document.getElementById('pg-current-text').innerText = "0 / 0";
            document.getElementById('pg-prev-btn').disabled = true;
            document.getElementById('pg-next-btn').disabled = true;
            return;
        }
        
        let totalPages = Math.ceil(window.filteredSuppliers.length / window.pageSize);
        if (window.currentPage > totalPages) window.currentPage = totalPages;
        if (window.currentPage < 1) window.currentPage = 1;
        
        let startIdx = (window.currentPage - 1) * window.pageSize;
        let endIdx = startIdx + window.pageSize;
        
        for (let i = startIdx; i < endIdx && i < window.filteredSuppliers.length; i++) {
            window.filteredSuppliers[i].element.style.display = 'table-row';
        }
        
        let showingEnd = Math.min(endIdx, window.filteredSuppliers.length);
        document.getElementById('pg-info-text').innerHTML = `Showing <strong>${startIdx + 1}</strong> – <strong>${showingEnd}</strong> of <strong>${window.filteredSuppliers.length}</strong>`;
        document.getElementById('pg-current-text').innerText = `${window.currentPage} / ${totalPages}`;
        
        document.getElementById('pg-prev-btn').disabled = (window.currentPage === 1);
        document.getElementById('pg-next-btn').disabled = (window.currentPage === totalPages);
    }
    
    window.navigatePage = function(page) {
        let totalPages = Math.ceil(window.filteredSuppliers.length / window.pageSize);
        if (page >= 1 && page <= totalPages) {
            window.currentPage = page;
            window.renderPagination();
        }
    }
    
    window.updatePageSize = function(size) {
        if (size === '1000') { window.pageSize = 999999; } else { window.pageSize = parseInt(size); }
        window.currentPage = 1;
        window.renderPagination();
    }

    window.openModal = function(id) { document.getElementById(id).classList.remove('hidden'); }
    window.closeModal = function(id) { document.getElementById(id).classList.add('hidden'); }

    window.openSupplierModalPopup = function(id, tab = null) {
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
                    
                    if (tab) { window.switchModalTab(tab); } 
                    else { window.switchModalTab('ledger'); }
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

    window.closeSupplierProfile = function() {
        document.getElementById('supplierProfileModal').classList.add('hidden');
        window.history.pushState({ path: '<?= APP_URL ?>/supplier' }, '', '<?= APP_URL ?>/supplier');
    }

    window.switchModalTab = function(tabName) {
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

    console.log("--- INLINE SCRIPT EXECUTED SUCCESSFULLY! ---");
} catch(e) {
    console.error("--- INLINE SCRIPT FAILED ---", e);
}


