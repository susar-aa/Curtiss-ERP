<script>
(function() {

    const globalBankAccounts = <?php echo json_encode($data['bank_accounts'] ?? []); ?>;
    const globalAllAccounts = <?php echo json_encode($data['all_accounts'] ?? []); ?>;

    function buildAccountOptions(selectedId, fallbackCode) {
        let html = '<option value="">-- Select Account --</option>';
        let hasSelected = false;
        if (selectedId !== undefined && selectedId !== null && selectedId !== '') {
            hasSelected = globalAllAccounts.some(acc => String(acc.id) === String(selectedId));
        }
        globalAllAccounts.forEach(acc => {
            let isSel = false;
            if (hasSelected) {
                isSel = String(acc.id) === String(selectedId);
            } else {
                isSel = acc.account_code === fallbackCode;
            }
            html += `<option value="${acc.id}" ${isSel ? 'selected' : ''}>${acc.account_code} - ${acc.account_name}</option>`;
        });
        return html;
    }

    // Helper function to resolve ID by account code
    function getAccountIdByCode(code) {
        const acc = globalAllAccounts.find(a => a.account_code === code);
        return acc ? acc.id : null;
    }

    let currentRouteId = null;
    let routeMap = null;
    let routeMapLayers = [];
    let rbSlotsCount = 2;
    let activeRouteBills = [];
    let currentDeliveryDetails = null;

    const pathGreenIcon = new L.Icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34]
    });
    const pathRedIcon = new L.Icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34]
    });
    const pathBlueIcon = new L.Icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34]
    });

    let isFetchingRoutes = false;
    function fetchRoutesList(pageNumber = 1) {
        if (isFetchingRoutes) return;
        isFetchingRoutes = true;

        const query = document.getElementById('floatingSearchInput').value.trim();
        const selectedRep = document.getElementById('filterRepSelect').value;
        const selectedRoute = document.getElementById('filterRouteSelect').value;
        const selectedDate = document.getElementById('filterDateInput').value;
        const selectedTerritory = document.getElementById('filterTerritorySelect').value;

        const params = new URLSearchParams();
        params.set('ajax', '1');
        params.set('page', pageNumber);
        if (query) params.set('search', query);
        if (selectedRep) params.set('rep', selectedRep);
        if (selectedRoute) params.set('route', selectedRoute);
        if (selectedDate) params.set('date', selectedDate);
        if (selectedTerritory) params.set('territory', selectedTerritory);
        
        const url = new URL(window.location);
        url.searchParams.set('page', pageNumber);
        if (query) url.searchParams.set('search', query); else url.searchParams.delete('search');
        if (selectedRep) url.searchParams.set('rep', selectedRep); else url.searchParams.delete('rep');
        if (selectedRoute) url.searchParams.set('route', selectedRoute); else url.searchParams.delete('route');
        if (selectedDate) url.searchParams.set('date', selectedDate); else url.searchParams.delete('date');
        if (selectedTerritory) url.searchParams.set('territory', selectedTerritory); else url.searchParams.delete('territory');
        window.history.replaceState({}, '', url);

        fetch(window.location.pathname + '?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            isFetchingRoutes = false;
            if (data.status === 'success') {
                const container = document.getElementById('routeListItemsContainer');
                const pagContainer = document.getElementById('routePaginationContainer');
                if (container) container.innerHTML = data.routes_html;
                if (pagContainer) pagContainer.innerHTML = data.pagination_html;
                
                if (currentRouteId) {
                    const activeRouteEl = document.getElementById('route_' + currentRouteId);
                    if (activeRouteEl) {
                        activeRouteEl.classList.add('active');
                    }
                } else {
                    const urlParams = new URLSearchParams(window.location.search);
                    const rId = urlParams.get('route_id');
                    if (rId) {
                        const routeEl = document.getElementById('route_' + rId);
                        if (routeEl) {
                            loadRouteDetails(rId, routeEl);
                            routeEl.scrollIntoView({ block: 'nearest' });
                        }
                    }
                }
            } else {
                console.error('Error fetching routes:', data.message);
            }
        })
        .catch(err => {
            isFetchingRoutes = false;
            console.error('AJAX fetch routes error:', err);
        });
    }

    function changePage(pageNumber) {
        fetchRoutesList(pageNumber);
    }

    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const routeId = urlParams.get('route_id');
        const page = urlParams.get('page') || 1;
        
        if (urlParams.get('search')) {
            document.getElementById('floatingSearchInput').value = urlParams.get('search');
        }
        if (urlParams.get('rep')) {
            document.getElementById('filterRepSelect').value = urlParams.get('rep');
        }
        if (urlParams.get('route')) {
            document.getElementById('filterRouteSelect').value = urlParams.get('route');
        }
        if (urlParams.get('date')) {
            document.getElementById('filterDateInput').value = urlParams.get('date');
        }
        if (urlParams.get('territory')) {
            document.getElementById('filterTerritorySelect').value = urlParams.get('territory');
        }

        fetchRoutesList(page);
    });

    function filterLeftPane(type, btn) {
        // Backwards compatibility stub
    }

    let searchDebounceTimeout = null;
    function searchRouteList() {
        if (searchDebounceTimeout) clearTimeout(searchDebounceTimeout);
        searchDebounceTimeout = setTimeout(() => {
            fetchRoutesList(1);
        }, 300);
    }

    function clearFilters() {
        document.getElementById('filterRepSelect').value = '';
        document.getElementById('filterRouteSelect').value = '';
        document.getElementById('filterDateInput').value = '';
        document.getElementById('filterTerritorySelect').value = '';
        document.getElementById('floatingSearchInput').value = '';
        fetchRoutesList(1);
    }

    function openRouteSwitcherModal() {
        const modal = document.getElementById('routeSwitcherModalBackdrop');
        if (modal) {
            modal.style.display = 'flex';
            document.getElementById('routeSwitcherSearchInput').value = '';
            searchRouteSwitcherList();
            document.getElementById('routeSwitcherSearchInput').focus();
        }
    }

    function closeRouteSwitcherModal() {
        const modal = document.getElementById('routeSwitcherModalBackdrop');
        if (modal) modal.style.display = 'none';
    }

    function openCreateRouteModal() {
        const modal = document.getElementById('createManualRouteModal');
        if (modal) {
            modal.style.display = 'flex';
            // Set current date/time to now
            const now = new Date();
            const offset = now.getTimezoneOffset() * 60000;
            const localISOTime = (new Date(now - offset)).toISOString().slice(0, 16);
            document.getElementById('mrStartTime').value = localISOTime;
        }
    }

    function closeCreateRouteModal() {
        const modal = document.getElementById('createManualRouteModal');
        if (modal) modal.style.display = 'none';
    }

    function searchRouteSwitcherList() {
        const query = document.getElementById('routeSwitcherSearchInput').value.toLowerCase().trim();
        document.querySelectorAll('.switcher-route-item').forEach(item => {
            const rname = item.getAttribute('data-rname').toLowerCase();
            const rep = item.getAttribute('data-rep').toLowerCase();
            if (rname.includes(query) || rep.includes(query)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function selectRouteFromSwitcher(routeId) {
        closeRouteSwitcherModal();
        const routeEl = document.getElementById('route_' + routeId);
        loadRouteDetails(routeId, routeEl);
        
        // Update URL query param to reflect the new route active without reloading
        const url = new URL(window.location);
        url.searchParams.set('route_id', routeId);
        window.history.replaceState({}, '', url);
    }

    function goBackToRoutes() {
        currentRouteId = null;
        document.body.classList.remove('workspace-showing');
        document.querySelector('.app-workspace').classList.remove('workspace-active');
        
        document.querySelectorAll('.route-item').forEach(i => i.classList.remove('active'));
        
        // Clear route_id query parameter from the URL
        const url = new URL(window.location);
        url.searchParams.delete('route_id');
        window.history.replaceState({}, '', url);
    }

    const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    let currentTabIndex = 1;
    let currentRouteStatus = 'Active';

    // Fetch wrapper to inject CSRF token to headers and bodies
    function fetchSecure(url, options = {}) {
        options.headers = options.headers || {};
        options.headers['X-CSRF-TOKEN'] = CSRF_TOKEN;
        
        if (options.body && typeof options.body === 'string') {
            try {
                const parsed = JSON.parse(options.body);
                if (typeof parsed === 'object' && parsed !== null) {
                    parsed.csrf_token = CSRF_TOKEN;
                    options.body = JSON.stringify(parsed);
                }
            } catch (e) {
                // Ignore parsing errors
            }
        }
        return fetch(url, options);
    }

    // Observer trigger to refresh Loading and Variance stages
    function onRouteDataChanged() {
        if (!currentRouteId) return;
        loadLoadingStage(currentRouteId);
        loadVarianceAdjustmentStage(currentRouteId);
    }

    function updateSidebarProgress() {
        const steps = [
            { id: 1, num: 1, name: 'Route Details', statusKey: 'Active' },
            { id: 3, num: 2, name: 'Bill Adjustments', statusKey: 'Adjustments' },
            { id: 4, num: 3, name: 'Loading', statusKey: 'Loading' },
            { id: 5, num: 4, name: 'Variance Audit', statusKey: 'Variance Adjustment' },
            { id: 6, num: 5, name: 'Delivery Arrange', statusKey: 'Finalizing' },
            { id: 7, num: 6, name: 'Reconciliation', statusKey: 'Finalizing' },
            { id: 8, num: 7, name: 'Delivery Execution', statusKey: 'Finalizing' },
            { id: 9, num: 8, name: 'Return Stock', statusKey: 'Finalizing' },
            { id: 10, num: 9, name: 'Payments', statusKey: 'Finalizing' },
            { id: 11, num: 10, name: 'Finalize', statusKey: 'Finalizing' }
        ];

        const statusSequence = ['Active', 'Pending GL', 'Adjustments', 'Loading', 'Variance Adjustment', 'Finalizing', 'Completed', 'Finalized'];
        let checkStatus = currentRouteStatus;
        if (checkStatus === 'Delivery Arranged') {
            checkStatus = 'Finalizing';
        }
        const currentRouteStatusIndex = statusSequence.indexOf(checkStatus);

        steps.forEach(step => {
            const el = document.getElementById('sb-step-' + step.id);
            if (!el) return;

            // Remove all states
            el.classList.remove('active', 'completed', 'pending', 'locked');
            
            // Step dot element
            const dot = el.querySelector('.step-dot');
            dot.innerHTML = step.num; // Default back to step sequence number

            // Determine active tab
            if (step.id === currentTabIndex) {
                el.classList.add('active');
            }

            let stepRequiredStatusIndex = statusSequence.indexOf(step.statusKey);
            let isStepCompleted = false;

            // Mark as completed if the route's current status is past the step's required status
            if (currentRouteStatus === 'Completed' || currentRouteStatus === 'Finalized') {
                isStepCompleted = true;
            } else if (currentRouteStatusIndex > stepRequiredStatusIndex) {
                isStepCompleted = true;
            } else if (currentRouteStatus === 'Finalizing' || currentRouteStatus === 'Delivery Arranged') {
                // Inside Finalizing, sub-stages can have completion heuristics:
                const d = document.getElementById('route_data_' + currentRouteId);
                const delId = d ? d.getAttribute('data-delivery-id') : null;
                const delStatus = d ? d.getAttribute('data-delivery-status') : null;

                if (step.id === 6 && delId && delId !== '0' && delId !== '') {
                    isStepCompleted = true; // Arranged is completed if delivery ID exists
                } else if (step.id === 7) {
                    // Check if reconciliation draft was saved
                    const cashVal = parseFloat(document.getElementById('reconActualCash')?.value || 0);
                    if (cashVal > 0) isStepCompleted = true;
                } else if (step.id === 8 && (delStatus === 'Finalizing' || delStatus === 'Completed')) {
                    isStepCompleted = true; // Delivery is completed if delivery status is Completed or Finalizing (meaning driver ended the route)
                } else if (step.id === 9) {
                    // Check if stock verified checkbox is checked OR return stock has been saved in delivery details
                    const verifyStockCheck = document.getElementById('settleVerifyStock');
                    let isVerified = verifyStockCheck ? verifyStockCheck.checked : false;
                    if (!isVerified && currentDeliveryDetails && currentDeliveryDetails.delivery && currentDeliveryDetails.delivery.return_stock_json !== null && currentDeliveryDetails.delivery.return_stock_json !== '') {
                        console.log("[SidebarProgress] Found saved return stock in database. Auto-checking checkbox.");
                        isVerified = true;
                        if (verifyStockCheck) {
                            verifyStockCheck.checked = true;
                        }
                    }
                    if (isVerified) {
                        isStepCompleted = true;
                    }
                } else if (step.id === 10) {
                    let allCollectionsApproved = true;
                    let hasChks = false;
                    document.querySelectorAll('.settle-payment-chk').forEach(chk => {
                        hasChks = true;
                        if (!chk.checked) allCollectionsApproved = false;
                    });
                    if (hasChks && allCollectionsApproved) {
                        isStepCompleted = true;
                    }
                } else if (step.id === 11) {
                    if (currentRouteStatus === 'Completed' || currentRouteStatus === 'Finalized') {
                        isStepCompleted = true;
                    }
                }
            }

            if (isStepCompleted) {
                el.classList.add('completed');
                dot.innerHTML = '<i class="fa-solid fa-check"></i>';
            } else {
                el.classList.add('pending');
            }
        });
    }

    function updateWizardProgress(status) {
        updateSidebarProgress();
    }

    function loadRouteDetails(routeId, el) {
        currentRouteId = routeId;
        
        document.querySelectorAll('.route-item').forEach(i => i.classList.remove('active'));
        if (el) el.classList.add('active');
        else {
            const sidebarEl = document.getElementById('route_' + routeId);
            if (sidebarEl) sidebarEl.classList.add('active');
        }

        const d = document.getElementById('route_data_' + routeId);
        const routeName = d.getAttribute('data-rname');
        const repName = d.getAttribute('data-rep');
        const status = d.getAttribute('data-status');
        const bindingId = d.getAttribute('data-binding-id');
        const isBound = d.getAttribute('data-bound') === '1';

        currentRouteStatus = status;

        document.getElementById('mhRouteName').innerText = routeName;
        document.getElementById('mhRepName').innerText = repName;
        document.getElementById('mhStart').innerText = d.getAttribute('data-start');
        document.getElementById('mhEnd').innerText = d.getAttribute('data-end');
        document.getElementById('mhSales').innerText = d.getAttribute('data-sales');
        document.getElementById('mhBills').innerText = d.getAttribute('data-bills');

        const formattedRouteNo = '#RT-' + String(routeId).padStart(5, '0');
        document.getElementById('mhRouteNumber').innerText = 'Route ' + formattedRouteNo;
        
        const statusBadge = document.getElementById('mhRouteStatusBadge');
        if (statusBadge) {
            statusBadge.innerText = status;
            statusBadge.style.background = (status === 'Completed' || status === 'Finalized') ? '#e2f0d9' : '#fff3cd';
            statusBadge.style.color = (status === 'Completed' || status === 'Finalized') ? '#2e7d32' : '#d97706';
            statusBadge.style.borderColor = (status === 'Completed' || status === 'Finalized') ? '#2e7d32' : '#d97706';
        }

        const boundSummary = document.getElementById('boundRouteSummaryContainer');
        if (boundSummary) {
            boundSummary.style.display = 'none';
        }
        const isMerged = d.getAttribute('data-merged') === '1';
        if (isMerged) {
            fetchSecure('<?= APP_URL ?>/RepTracking/api_get_bound_routes_summary/' + routeId)
                .then(res => res.json())
                .then(resData => {
                    if (resData.status === 'success') {
                        document.getElementById('brsConstituentsList').innerText = resData.constituents.map(c => `${c.route_name} (ID: #${c.id})`).join(', ');
                        document.getElementById('brsTotalCustomers').innerText = resData.total_customers;
                        document.getElementById('brsTotalInvoices').innerText = resData.total_invoices;
                        document.getElementById('brsTotalValue').innerText = 'Rs ' + parseFloat(resData.total_value).toLocaleString('en-IN', {minimumFractionDigits:2});
                        document.getElementById('brsTotalProducts').innerText = `${resData.unique_products} unique items (Total Qty: ${resData.total_products_qty})`;
                        if (boundSummary) {
                            boundSummary.style.display = 'block';
                        }
                    }
                });
        }

        document.getElementById('midHeader').style.visibility = 'visible';
        document.getElementById('midEmptyState').style.display = 'none';
        document.getElementById('workspaceLayoutWrapper').style.display = 'flex';
        document.getElementById('btnViewMap').style.display = 'inline-flex';

        const btnUnbind = document.getElementById('btnUnbindRoute');
        if (btnUnbind) {
            if (isBound && bindingId) {
                btnUnbind.style.display = 'inline-flex';
                btnUnbind.setAttribute('data-binding-id', bindingId);
            } else {
                btnUnbind.style.display = 'none';
            }
        }

        updateWizardProgress(status);

        document.getElementById('routeWorkspaceTabs').style.display = 'flex';
        document.getElementById('stageContentWrapper').style.display = 'block';

        // Toggle completed archive banner
        const archiveBanner = document.getElementById('completedArchiveBanner');
        if (archiveBanner) {
            if (status === 'Completed' || status === 'Finalized') {
                archiveBanner.style.display = 'flex';
            } else {
                archiveBanner.style.display = 'none';
            }
        }

        // Close slider
        closeInvoiceSlider();

        // Auto-advance active tab if entering a route that is in Finalizing/Completed/Finalized status
        if ((status === 'Finalizing' || status === 'Completed' || status === 'Finalized') && currentTabIndex < 7) {
            currentTabIndex = 7; // Land on Reconciliation
        }

        // Switch to the last selected index, default to 1 (Details)
        switchRouteTab(currentTabIndex);

        // Transition views
        document.body.classList.add('workspace-showing');
        document.querySelector('.app-workspace').classList.add('workspace-active');
    }

    function switchRouteTab(tabIndex) {
        currentTabIndex = tabIndex;
        
        // Update tab buttons styling
        const tabBtnMap = {
            1: 'auto-evt-button-4',
            3: 'auto-evt-button-6',
            4: 'auto-evt-button-7',
            5: 'auto-evt-button-8',
            6: 'auto-evt-button-9',
            7: 'auto-evt-button-10',
            8: 'auto-evt-button-11',
            9: 'auto-evt-button-12',
            10: 'auto-evt-button-13',
            11: 'btnTabFinalize'
        };
        document.querySelectorAll('#routeWorkspaceTabs .scroll-tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        const activeBtnId = tabBtnMap[tabIndex];
        if (activeBtnId) {
            document.getElementById(activeBtnId)?.classList.add('active');
        }
        
        // Toggle tab panels display
        document.querySelectorAll('.workspace-tab-panel').forEach(panel => {
            panel.style.display = 'none';
        });
        const activePanel = document.getElementById('tabpanel-' + tabIndex);
        if (activePanel) {
            activePanel.style.display = 'block';
        }
        
        updateSidebarProgress();
        if (!currentRouteId) return;
        
        // Dynamically load tab data
        switch (tabIndex) {
            case 1:
                loadTab1Details(currentRouteId);
                break;
            case 3:
                loadAdjustmentsStage(currentRouteId);
                break;
            case 4:
                loadLoadingStage(currentRouteId);
                break;
            case 5:
                loadVarianceAdjustmentStage(currentRouteId);
                break;
            case 6:
                loadDispatchStage(currentRouteId);
                break;
            case 7:
                loadTab8Reconciliation(currentRouteId);
                break;
            case 8:
                loadDeliveryLiveStage(currentRouteId);
                break;
            case 9:
                loadTab9ReturnStock(currentRouteId);
                break;
            case 10:
                loadTab10Accounting(currentRouteId);
                break;
            case 11:
                loadTab11Finalize(currentRouteId);
                break;
        }
    }

    function loadTab1Details(routeId) {
        const d = document.getElementById('route_data_' + routeId);
        if (!d) return;

        const formattedRouteNo = '#RT-' + String(routeId).padStart(5, '0');

        document.getElementById('tab1RouteNumber').innerText = formattedRouteNo;
        document.getElementById('tab1RouteName').innerText = d.getAttribute('data-rname') || '';
        document.getElementById('tab1RepName').innerText = d.getAttribute('data-rep') || '';
        
        const status = d.getAttribute('data-status') || '';
        const statusBadge = document.getElementById('tab1Status');
        if (statusBadge) {
            statusBadge.innerText = status;
            statusBadge.style.background = (status === 'Completed' || status === 'Finalized') ? '#e2f0d9' : '#fff3cd';
            statusBadge.style.color = (status === 'Completed' || status === 'Finalized') ? '#2e7d32' : '#d97706';
            statusBadge.style.borderColor = (status === 'Completed' || status === 'Finalized') ? '#2e7d32' : '#d97706';
        }

        document.getElementById('tab1SalesValue').innerText = 'Rs ' + (d.getAttribute('data-sales') || '0.00');
        document.getElementById('tab1BillsCount').innerText = d.getAttribute('data-bills') || '0';

        document.getElementById('tab1StartTime').innerText = d.getAttribute('data-start-time') || '';
        document.getElementById('tab1EndTime').innerText = d.getAttribute('data-end-time') || '';
        document.getElementById('tab1StartMeter').innerText = d.getAttribute('data-start') || '';
        document.getElementById('tab1EndMeter').innerText = d.getAttribute('data-end') || '';
        
        const start = parseFloat(d.getAttribute('data-start')) || 0;
        const end = parseFloat(d.getAttribute('data-end')) || 0;
        document.getElementById('tab1Distance').innerText = (end > start) ? (end - start) + ' km' : 'Active';

        const isReadOnly = (currentRouteStatus === 'Completed' || currentRouteStatus === 'Finalized');
        const notesTextarea = document.getElementById('tab1RouteNotes');
        const saveNotesBtn = document.getElementById('btnSaveRouteNotes');

        notesTextarea.readOnly = isReadOnly;
        saveNotesBtn.disabled = isReadOnly;
        saveNotesBtn.style.opacity = isReadOnly ? '0.5' : '1';
        saveNotesBtn.style.cursor = isReadOnly ? 'not-allowed' : 'pointer';

        fetchSecure('<?= APP_URL ?>/RepTracking/api_get_route_details/' + routeId)
            .then(res => res.json())
            .then(data => {
                notesTextarea.value = data.notes || '';
            });
    }

    function saveRouteNotes() {
        if (!currentRouteId) return;
        const notes = document.getElementById('tab1RouteNotes').value;
        fetchSecure('<?= APP_URL ?>/RepTracking/api_save_route_notes', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ route_id: currentRouteId, notes: notes })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("Route notes saved successfully!");
            } else {
                alert("Error: " + data.message);
            }
        });
    }



    let cachedOutstandingBills = [];
    let selectedCreditBills = [];

    function loadOutstandingBillsChecklist(routeId, delId) {
        const container = document.getElementById('adjDaBillsContainer');
        if (!container) return;

        container.innerHTML = '<p style="text-align:center; color:#888;">Loading credit bills... </p>';
        
        // Reset cached variables
        cachedOutstandingBills = [];
        selectedCreditBills = [];
        
        // Clear search inputs
        const searchInput = document.getElementById('creditBillsSearch');
        if (searchInput) searchInput.value = '';

        const loadBillsData = () => {
            fetchSecure('<?= APP_URL ?>/RepTracking/api_get_outstanding_bills/' + routeId)
                .then(res => res.json())
                .then(data => {
                    if (data.status !== 'success' || !data.bills) {
                        container.innerHTML = '<p style="text-align:center; color:#888; margin:10px 0;">Error loading outstanding credit bills.</p>';
                        return;
                    }
                    cachedOutstandingBills = data.bills;
                    
                    // Populate route filter dropdown
                    const routeFilter = document.getElementById('creditBillsRouteFilter');
                    if (routeFilter) {
                        routeFilter.innerHTML = `
                            <option value="all">All Routes</option>
                            <option value="none">No Route / Unassigned</option>
                        `;
                        
                        // Extract unique routes
                        const routeMap = {};
                        cachedOutstandingBills.forEach(cust => {
                            cust.bills.forEach(b => {
                                if (b.rep_route_id && b.route_name) {
                                    routeMap[b.rep_route_id] = b.route_name;
                                }
                            });
                        });
                        
                        // Add options to filter
                        Object.keys(routeMap).forEach(rid => {
                            const opt = document.createElement('option');
                            opt.value = rid;
                            opt.textContent = routeMap[rid];
                            routeFilter.appendChild(opt);
                        });
                    }

                    // Run initial filter rendering
                    filterCreditBillsList();
                });
        };

        if (delId && delId !== '0' && delId !== '') {
            fetchSecure('<?= APP_URL ?>/RepTracking/api_get_delivery_details/' + delId)
                .then(res => res.json())
                .then(dData => {
                    if (dData.delivery && dData.delivery.selected_credit_invoices) {
                        try {
                            selectedCreditBills = JSON.parse(dData.delivery.selected_credit_invoices).map(id => parseInt(id));
                        } catch (e) {}
                    }
                    loadBillsData();
                });
        } else {
            loadBillsData();
        }
    }

    function filterCreditBillsList() {
        const container = document.getElementById('adjDaBillsContainer');
        if (!container) return;

        const isReadOnly = (currentRouteStatus === 'Completed' || currentRouteStatus === 'Finalized');
        const searchInput = document.getElementById('creditBillsSearch');
        const routeFilter = document.getElementById('creditBillsRouteFilter');
        
        const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedRoute = routeFilter ? routeFilter.value : 'all';

        let filteredBillsCount = 0;
        let html = '<div style="display:flex; flex-direction:column; gap:8px;">';

        cachedOutstandingBills.forEach(cust => {
            const customerName = cust.customer_name.toLowerCase();
            const customerMatches = customerName.includes(searchQuery);

            const matchedBills = cust.bills.filter(b => {
                const invoiceNumber = b.invoice_number.toLowerCase();
                const invoiceMatches = invoiceNumber.includes(searchQuery);

                if (searchQuery && !customerMatches && !invoiceMatches) {
                    return false;
                }

                if (selectedRoute === 'none') {
                    if (b.rep_route_id) return false;
                } else if (selectedRoute !== 'all') {
                    if (String(b.rep_route_id) !== String(selectedRoute)) return false;
                }

                return true;
            });

            if (matchedBills.length > 0) {
                html += `
                    <div style="margin-bottom:5px; border-bottom: 0.5px solid var(--c-separator); padding-bottom: 5px;">
                        <div style="font-weight:700; font-size:12px; color:var(--t-primary); background:var(--c-surface2); padding:4px 8px; border-radius:var(--r-xs); display:flex; justify-content:space-between; align-items:center;">
                            <span>${cust.customer_name}</span>
                            <span style="font-weight:normal; font-size:10px; color:#64748b;">${cust.mca_name}</span>
                        </div>
                        <div style="padding-left:8px;">
                `;

                matchedBills.forEach(b => {
                    filteredBillsCount++;
                    let amtFormatted = parseFloat(b.true_grand_total).toLocaleString('en-IN', {minimumFractionDigits:2});
                    const isChecked = selectedCreditBills.includes(parseInt(b.id)) ? 'checked' : '';
                    const routeTag = b.route_name ? `<span style="font-size:9px; background:#e0f2fe; color:#0369a1; padding:1px 4px; border-radius:3px; margin-left:5px; font-weight:600;">${b.route_name}</span>` : `<span style="font-size:9px; background:#f1f5f9; color:#475569; padding:1px 4px; border-radius:3px; margin-left:5px; font-weight:600;">No Route</span>`;
                    
                    html += `
                        <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer; padding:6px; border-bottom:0.5px dashed var(--c-separator);">
                            <input type="checkbox" class="adj-da-bill-checkbox" value="${b.id}" style="width:16px; height:16px; margin-top:2px;" ${isReadOnly ? 'disabled' : ''} ${isChecked} onchange="toggleCreditBillSelection(this)">
                            <div style="flex:1;">
                                <div style="font-weight:bold; font-size:12px; color:var(--t-primary);">${b.invoice_number} ${routeTag}</div>
                                <div style="font-size:11px; color:var(--t-secondary);">Date: ${b.invoice_date}</div>
                            </div>
                            <div style="font-weight:bold; font-family:monospace; color:#c62828; font-size:12px; margin-top:2px;">Rs ${amtFormatted}</div>
                        </label>
                    `;
                });

                html += `
                        </div>
                    </div>
                `;
            }
        });

        html += '</div>';

        if (filteredBillsCount === 0) {
            container.innerHTML = '<p style="text-align:center; color:#888; margin:10px 0; font-size:12px;">No credit bills match the search/filter criteria.</p>';
        } else {
            container.innerHTML = html;
        }
    }

    function toggleCreditBillSelection(checkbox) {
        const billId = parseInt(checkbox.value);
        if (checkbox.checked) {
            if (!selectedCreditBills.includes(billId)) {
                selectedCreditBills.push(billId);
            }
        } else {
            selectedCreditBills = selectedCreditBills.filter(id => id !== billId);
        }
    }

    function loadAdjustmentsStage(routeId) {
        const tbody = document.getElementById('adjustmentsInvoicesTbody');
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Loading Sales Orders... </td></tr>';

        const isReadOnly = (currentRouteStatus === 'Completed' || currentRouteStatus === 'Finalized');

        // Hide/Show operational buttons header in Tab 3
        const opsHeader = document.querySelector("#tabpanel-3 button")?.parentElement;
        if (opsHeader) {
            opsHeader.style.display = isReadOnly ? 'none' : 'flex';
        }

        // Load outstanding bills and pre-check already selected ones if delivery is already arranged
        const rdata = document.getElementById('route_data_' + routeId);
        const delId = rdata ? rdata.getAttribute('data-delivery-id') : null;
        loadOutstandingBillsChecklist(routeId, delId);

        fetchSecure('<?= APP_URL ?>/RepTracking/api_get_route_details/' + routeId)
            .then(res => res.json())
            .then(data => {
                const bills = data.bills || [];
                tbody.innerHTML = '';
                if (bills.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:20px; color:#888;">No sales orders attached to this route.</td></tr>';
                } else {
                    bills.forEach(bill => {
                        let time = new Date(bill.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        
                        let dropdownHtml = '';
                        if (!isReadOnly) {
                            dropdownHtml += `
                                <button class="dots-dropdown-item" onclick="event.stopPropagation(); editSalesOrder(${bill.id})">
                                    <i class="ph ph-pencil"></i> Edit
                                </button>
                                <button class="dots-dropdown-item danger" onclick="event.stopPropagation(); confirmDeleteSalesOrder(${bill.id}, '${bill.invoice_number}')">
                                    <i class="ph ph-trash"></i> Delete
                                </button>
                                <button class="dots-dropdown-item" onclick="event.stopPropagation(); detachInvoice(${bill.id})">
                                    <i class="ph ph-link-break"></i> Remove
                                </button>
                                <button class="dots-dropdown-item" onclick="event.stopPropagation(); openMoveInvoiceModal(${bill.id}, '${bill.invoice_number}')">
                                    <i class="ph ph-arrow-square-out"></i> Move
                                </button>
                                <div class="dots-dropdown-divider"></div>
                            `;
                        }
                        dropdownHtml += `
                            <button class="dots-dropdown-item" onclick="event.stopPropagation(); openInvoiceSlider(${bill.id})">
                                <i class="ph ph-eye"></i> View Invoice
                            </button>
                            <button class="dots-dropdown-item" onclick="event.stopPropagation(); printInvoice(${bill.id})">
                                <i class="ph ph-printer"></i> Print
                            </button>
                            <button class="dots-dropdown-item" data-customer="${bill.customer_name.replace(/"/g, '&quot;')}" onclick="event.stopPropagation(); viewCustomerProfile(this.getAttribute('data-customer'))">
                                <i class="ph ph-user"></i> View Customer
                            </button>
                            <button class="dots-dropdown-item" onclick="event.stopPropagation(); downloadInvoicePdf(${bill.id})">
                                <i class="ph ph-file-pdf"></i> Download PDF
                            </button>
                            <button class="dots-dropdown-item" onclick="event.stopPropagation(); exportInvoiceExcel(${bill.id})">
                                <i class="ph ph-file-xls"></i> Export Excel
                            </button>
                        `;

                        let actionBtn = `
                            <div class="dots-menu-container">
                                <button class="dots-btn" onclick="toggleDotsMenu(event, ${bill.id})">
                                    <i class="ph-bold ph-dots-three-vertical"></i>
                                </button>
                                <div class="dots-dropdown" id="dots-dropdown-${bill.id}">
                                    ${dropdownHtml}
                                </div>
                            </div>
                        `;

                        tbody.innerHTML += `
                            <tr>
                                <td style="font-weight:bold; color:var(--primary); cursor:pointer;" onclick="openInvoiceSlider(${bill.id})">${bill.invoice_number}</td>
                                <td>${time}</td>
                                <td><strong>${bill.customer_name}</strong></td>
                                <td style="text-align:right; font-family:monospace; font-weight:bold;">${parseFloat(bill.true_grand_total).toLocaleString('en-IN', {minimumFractionDigits:2})}</td>
                                <td style="text-align:center;">${actionBtn}</td>
                            </tr>
                        `;
                    });
                }
            });
    }

    function detachInvoice(invoiceId) {
        if (!confirm("Are you sure you want to remove/detach this Sales Order from this route?")) return;
        fetchSecure('<?= APP_URL ?>/RepTracking/api_detach_invoice', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ invoice_id: invoiceId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("Sales Order successfully detached from route!");
                onRouteDataChanged();
                loadAdjustmentsStage(currentRouteId);
            } else {
                alert("Error: " + data.message);
            }
        });
    }

    function submitAdjustmentsLogisticsArrange() {
        const date = document.getElementById('adjDaDate').value;
        const vehicle = document.getElementById('adjDaVehicle').value;
        const driver = document.getElementById('adjDaDriver').value;
        const partner = document.getElementById('adjDaPartner').value;

        if (!vehicle) { alert("Please select a Vehicle Number."); return; }
        if (!driver) { alert("Please select a Driver Name."); return; }

        const checkedBills = [...selectedCreditBills];

        const payload = {
            rep_route_id: currentRouteId,
            secondary_rep_route_id: null,
            delivery_date: date,
            vehicle_number: vehicle,
            driver_name: driver,
            partner_name: partner,
            selected_credit_invoices: checkedBills
        };

        fetchSecure('<?= APP_URL ?>/RepTracking/arrange', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("Delivery arranged successfully!");
                const rdata = document.getElementById('route_data_' + currentRouteId);
                if (rdata) {
                    rdata.setAttribute('data-delivery-id', data.delivery_id);
                }
                onRouteDataChanged();
                loadDispatchStage(currentRouteId);
            } else {
                alert("Error: " + data.message);
            }
        });
    }

    function loadLoadingStage(routeId) {
        const box = document.getElementById('loadingBox');
        if (!box) return;
        box.innerHTML = 'Loading loading items checklist... ';

        fetchSecure('<?= APP_URL ?>/RepTracking/api_get_route_variances/' + routeId)
            .then(res => {
                if (!res.ok) {
                    return res.json().then(errData => {
                        throw new Error(errData.message || 'Server error ' + res.status);
                    }).catch(() => {
                        throw new Error('HTTP error ' + res.status);
                    });
                }
                return res.json();
            })
            .then(data => {
                if (data.status !== 'success') {
                    box.innerHTML = `<p style="color:red; text-align:center; padding:10px;">Error loading data: ${data.message || 'Unknown error'}</p>`;
                    return;
                }

                const deliveries = data.deliveries || [];
                const loadingItems = data.loading_items || [];

                // Verification has started if we have at least one delivery AND its verified_items count is > 0.
                const hasVerificationStarted = (deliveries.length > 0 && deliveries[0].verified_items > 0);

                let listHtml = '';
                let printButtonsHtml = `
                    <div style="margin-bottom: 15px; text-align: right; display: flex; justify-content: flex-end; gap: 10px;">
                        <button class="btn btn-primary" onclick="printLoadingSheet('final')" style="padding:8px 16px; background:#3f51b5; border:none; color:#fff; border-radius:4px; font-weight:bold; font-size:12px; cursor:pointer; display:inline-flex; align-items:center; gap:6px;"><i class="ph ph-printer"></i> Print Loading Sheet</button>
                    </div>
                `;

                if (!hasVerificationStarted) {
                    if (loadingItems.length === 0) {
                        listHtml = '<tr><td colspan="2" style="text-align:center; padding:15px; color:#64748b;">No products required for loading on this route.</td></tr>';
                    } else {
                        loadingItems.forEach(item => {
                            listHtml += `
                                <tr style="border-bottom:1px solid #e2e8f0;">
                                    <td style="padding:10px; font-weight:600; color:#1e293b;">${item.item_name}</td>
                                    <td style="padding:10px; text-align:center; font-weight:bold; font-family:monospace; font-size:13px;">${item.total_qty}</td>
                                </tr>
                            `;
                        });
                    }

                    box.innerHTML = `
                        ${printButtonsHtml}
                        <div style="background:#fff7ed; border:1px solid #ffedd5; padding:12px; border-radius:6px; margin-bottom:15px; color:#c2410c; font-size:12px; font-weight:600; display:flex; align-items:center; gap:6px;">
                            <i class="ph ph-info"></i> Verification has not started yet. Showing original required loading quantities.
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr style="background:#f8fafc;">
                                    <th style="padding:10px; text-align:left;">Product Name</th>
                                    <th style="padding:10px; text-align:center; width:150px;">Required Qty</th>
                                </tr>
                            </thead>
                            <tbody>${listHtml}</tbody>
                        </table>
                    `;
                } else {
                    const del = deliveries[0];
                    if (del.items.length === 0) {
                        listHtml = '<tr><td colspan="4" style="text-align:center; padding:15px; color:#64748b;">No verification records found.</td></tr>';
                    } else {
                        del.items.forEach(item => {
                            const req = parseFloat(item.required_qty);
                            const loaded = item.final_loaded_qty !== null ? parseFloat(item.final_loaded_qty) : parseFloat(item.pre_loaded_qty);
                            const diff = loaded - req;

                            let rowBg = '';
                            let diffIndicator = '';

                            if (Math.abs(diff) < 0.01) {
                                rowBg = 'background-color: #d1fae5; color: #065f46;';
                                diffIndicator = '<i class="ph ph-check-circle" style="color: #16a34a; font-size: 14px;"></i> Match';
                            } else if (diff < 0) {
                                rowBg = 'background-color: #fee2e2; color: #991b1b;';
                                diffIndicator = `Shortage (${diff.toFixed(1)})`;
                            } else {
                                rowBg = 'background-color: #fff3e0; color: #e65100;';
                                diffIndicator = `Overage (+${diff.toFixed(1)})`;
                            }

                            let nameHtml = `<div style="font-weight:600;">${item.item_name}</div>`;
                            if (item.replaced_by_name) {
                                nameHtml += `<div style="font-size:11px; color:#673ab7; font-weight:bold; margin-top:2px;">→ Replaced By ${item.replaced_by_name} (Qty: ${item.replacement_qty})</div>`;
                            } else if (item.replaces_name) {
                                nameHtml += `<div style="font-size:11px; color:#16a34a; font-weight:bold; margin-top:2px;"><i class="ph ph-star" style="color: #16a34a;"></i> Replacement for ${item.replaces_name}</div>`;
                            }

                            listHtml += `
                                <tr style="border-bottom:1px solid #e2e8f0; ${rowBg}">
                                    <td style="padding:10px;">${nameHtml}</td>
                                    <td style="padding:10px; text-align:center; font-weight:bold; font-family:monospace; font-size:13px;">${req}</td>
                                    <td style="padding:10px; text-align:center; font-weight:bold; font-family:monospace; font-size:13px;">${loaded}</td>
                                    <td style="padding:10px; text-align:center; font-weight:bold; font-size:12px;">${diffIndicator}</td>
                                </tr>
                            `;
                        });
                    }

                    box.innerHTML = `
                        ${printButtonsHtml}
                        <div style="display:flex; justify-content:space-between; align-items:center; background:#f1f5f9; padding:12px; border-radius:6px; margin-bottom:15px; font-size:12px;">
                            <div>Loading Sheet Verification ID: <strong>#${del.delivery_id}</strong></div>
                            <div>Verification Status: <strong>${del.verified_items} / ${del.total_items} verified</strong></div>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr style="background:#f8fafc;">
                                    <th style="padding:10px; text-align:left;">Product Name</th>
                                    <th style="padding:10px; text-align:center; width:120px;">Required Qty</th>
                                    <th style="padding:10px; text-align:center; width:120px;">Verified Qty</th>
                                    <th style="padding:10px; text-align:center; width:150px;">Status / Variance</th>
                                </tr>
                            </thead>
                            <tbody>${listHtml}</tbody>
                        </table>
                    `;
                }
            })
            .catch(err => {
                console.error("Error loading route variance details:", err);
                box.innerHTML = `<p style="color:red; text-align:center; padding:10px;">Error loading data: ${err.message}</p>`;
            });
    }

    let currentVarianceState = {};
    let currentSubstitutions = [];

    function loadVarianceAdjustmentStage(routeId) {
        const box = document.getElementById('varianceAuditBox');
        if (!box) return;
        box.innerHTML = '<div style="padding:20px; text-align:center;">Loading shortages & overages... </div>';
        currentVarianceState = {};
        currentSubstitutions = [];

        fetchSecure('<?= APP_URL ?>/RepTracking/api_get_route_variances/' + routeId)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success' || !data.deliveries || data.deliveries.length === 0) {
                    box.innerHTML = '<p style="color:red; padding:10px;">No variance records found.</p>';
                    return;
                }
                const del = data.deliveries[0];
                const items = del.items || [];
                currentSubstitutions = data.substitutions || [];
                
                if (items.length === 0) {
                    box.innerHTML = '<p style="color:green; padding:10px; font-weight:bold;">No products picked on this route.</p>';
                    return;
                }

                let fetchPromises = [];
                items.forEach(item => {
                    const itemId = item.item_id;
                    const varOptId = item.variation_option_id || 0;
                    const stateKey = itemId + '_' + varOptId;
                    currentVarianceState[stateKey] = {
                        item_id: itemId,
                        variation_option_id: item.variation_option_id || null,
                        item_name: item.item_name,
                        required_qty: parseFloat(item.required_qty),
                        pre_loaded_qty: parseFloat(item.pre_loaded_qty),
                        final_loaded_qty: item.final_loaded_qty !== null ? parseFloat(item.final_loaded_qty) : parseFloat(item.required_qty),
                        variance: parseFloat(item.variance),
                        invoices: []
                    };

                    if (parseFloat(item.variance) !== 0) {
                        let url = '<?= APP_URL ?>/RepTracking/api_get_product_invoices?route_id=' + routeId + '&item_id=' + itemId;
                        if (item.variation_option_id) {
                            url += '&variation_option_id=' + item.variation_option_id;
                        }
                        const p = fetchSecure(url)
                            .then(res => res.json())
                            .then(invData => {
                                if (invData.status === 'success') {
                                    currentVarianceState[stateKey].invoices = invData.invoices.map(inv => ({
                                        invoice_id: parseInt(inv.invoice_id),
                                        invoice_number: inv.invoice_number,
                                        customer_name: inv.customer_name,
                                        original_qty: parseFloat(inv.original_qty !== undefined ? inv.original_qty : inv.quantity),
                                        quantity: parseFloat(inv.quantity),
                                        unit_price: parseFloat(inv.unit_price),
                                        remove_completely: 0
                                    }));
                                }
                            });
                        fetchPromises.push(p);
                    }
                });

                Promise.all(fetchPromises).then(() => {
                    renderVarianceReconciliation();
                });
            });
    }

    function renderVarianceReconciliation() {
        const box = document.getElementById('varianceAuditBox');
        if (!box) return;
        let html = '';

        let totalShortages = 0;
        let totalOverages = 0;
        let hasUnbalanced = false;

        let tableRows = '';
        const isReadOnly = (currentRouteStatus === 'Completed' || currentRouteStatus === 'Finalized');

        Object.values(currentVarianceState).forEach(item => {
            const variance = item.variance;
            if (variance < 0) totalShortages += Math.abs(variance);
            if (variance > 0) totalOverages += variance;

            let varColor = '#2e7d32';
            let varText = 'Match (0)';
            if (variance < 0) {
                varColor = '#c62828';
                varText = `${variance} (Shortage)`;
            } else if (variance > 0) {
                varColor = '#ef6c00';
                varText = `+${variance} (Overage)`;
            }

            let allocatedSum = 0;
            if (variance === 0) {
                allocatedSum = item.final_loaded_qty;
            } else {
                item.invoices.forEach(inv => {
                    allocatedSum += inv.quantity;
                });
            }

            const isItemBalanced = (item.invoices.length === 0 || Math.abs(allocatedSum - item.final_loaded_qty) < 0.01);
            if (!isItemBalanced) {
                hasUnbalanced = true;
            }

            const statusBadge = isItemBalanced 
                ? `<span style="background:#d1fae5; color:#065f46; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:bold; display:inline-flex; align-items:center; gap:4px;"><i class="ph ph-check-circle" style="font-size:13px;"></i> Balanced</span>`
                : `<span style="background:#fee2e2; color:#991b1b; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:bold; display:inline-flex; align-items:center; gap:4px;"><i class="ph ph-warning" style="font-size:13px;"></i> Unbalanced</span>`;

            tableRows += `
                <tr style="border-bottom:1px solid #e2e8f0;">
                    <td style="padding:10px; font-weight:bold; color:#1e293b;">${item.item_name}</td>
                    <td style="padding:10px; text-align:center; font-weight:bold; font-family:monospace;">${item.required_qty}</td>
                    <td style="padding:10px; text-align:center; font-family:monospace;">${item.pre_loaded_qty}</td>
                    <td style="padding:10px; text-align:center; font-weight:bold; font-family:monospace; background:#f8fafc;">${item.final_loaded_qty}</td>
                    <td style="padding:10px; text-align:center; font-weight:bold; color:${varColor}; font-family:monospace;">${varText}</td>
                    <td style="padding:10px; text-align:center;">${statusBadge}</td>
                </tr>
            `;
        });

        // Substitutions Panel
        let substitutionHtml = '';
        if (currentSubstitutions.length > 0) {
            let subRows = '';
            currentSubstitutions.forEach(sub => {
                let actionPart = '';
                if (sub.status === 'Pending Bill Update') {
                    actionPart = `
                        <div style="display:flex; flex-direction:column; gap:8px; align-items:flex-end;">
                            <div>
                                <label style="font-size:11px; font-weight:bold; color:#475569; margin-right:5px;">Pricing Decision:</label>
                                <select id="pricing_choice_${sub.id}" style="padding:4px 8px; font-size:11px; border-radius:4px; border:1px solid #cbd5e1; background:#fff;">
                                    <option value="original">Use Original Product Price</option>
                                    <option value="replacement" selected>Use Replacement Product Price</option>
                                </select>
                            </div>
                            <button onclick="applyProductSubstitution(${sub.id})" style="padding:6px 12px; background:#673ab7; color:#fff; border:none; border-radius:4px; font-size:11px; font-weight:bold; cursor:pointer;">
                                Apply Substitution To Bills
                            </button>
                        </div>
                    `;
                } else {
                    actionPart = `
                        <div style="text-align:right; font-size:11px; color:#64748b; line-height:1.4;">
                            Applied by: <strong>${sub.creator_name || 'System'}</strong><br>
                            Date: <strong>${sub.applied_at}</strong><br>
                            Original Bill Value: <strong>Rs. ${parseFloat(sub.original_bill_value).toFixed(2)}</strong><br>
                            Updated Bill Value: <strong>Rs. ${parseFloat(sub.updated_bill_value).toFixed(2)}</strong>
                        </div>
                    `;
                }

                subRows += `
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; border-bottom:1px solid #e2e8f0; gap:15px;">
                        <div style="line-height:1.5; font-size:12px;">
                            Original Product: <strong style="color:#ef6c00;">${sub.original_item_name}</strong> (Required Qty: <strong>${parseFloat(sub.required_qty)}</strong>)<br>
                            Replacement Product: <strong style="color:#2e7d32;">${sub.replacement_item_name}</strong> (Loaded Qty: <strong>${parseFloat(sub.loaded_qty)}</strong>)<br>
                            Status: <span style="font-weight:bold; color:${sub.status === 'Applied' ? '#16a34a' : '#e65100'};">${sub.status}</span>
                        </div>
                        ${actionPart}
                    </div>
                `;
            });

            substitutionHtml = `
                <h5 style="margin:0 0 10px 0; font-size:13px; color:#475569; text-transform:uppercase; font-weight:bold; display:flex; align-items:center; gap:6px;"><i class="ph ph-swap"></i> Product Substitutions</h5>
                <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:10px; margin-bottom:25px;">
                    ${subRows}
                </div>
            `;
        }

        let reconciliationPanels = '';
        let hasAnyVariance = false;

        Object.values(currentVarianceState).forEach(item => {
            if (item.variance === 0) return;
            hasAnyVariance = true;

            const stateKey = item.item_id + '_' + (item.variation_option_id || 0);

            let subBadge = '';
            const subInfo = currentSubstitutions.find(s => parseInt(s.replacement_item_id) === parseInt(item.item_id) || parseInt(s.original_item_id) === parseInt(item.item_id));
            if (subInfo) {
                if (parseInt(subInfo.replacement_item_id) === parseInt(item.item_id)) {
                    subBadge = `<span style="display:inline-flex; align-items:center; gap:4px; margin-top:4px; padding:2px 6px; background:#eff6ff; color:#1d4ed8; border-radius:4px; font-size:10px; font-weight:bold; margin-bottom:5px;"><i class="ph ph-swap"></i> Replaced "${subInfo.original_item_name}"</span>`;
                } else {
                    subBadge = `<span style="display:inline-flex; align-items:center; gap:4px; margin-top:4px; padding:2px 6px; background:#fef2f2; color:#b91c1c; border-radius:4px; font-size:10px; font-weight:bold; margin-bottom:5px;"><i class="ph ph-swap"></i> Replaced by "${subInfo.replacement_item_name}"</span>`;
                }
            }

            let invoiceRows = '';
            let currentTotal = 0;

            item.invoices.forEach((inv, index) => {
                currentTotal += inv.quantity;

                 let actionSelect = '';
                 if (inv.quantity === 0) {
                     actionSelect = `
                         <div style="margin-top:5px; color:#dc2626; font-size:11px; font-weight:bold; display:flex; align-items:center; gap:4px;">
                             <i class="ph ph-warning"></i> Product will be removed from invoice
                         </div>
                     `;
                 }

                invoiceRows += `
                    <div style="display:grid; grid-template-columns:1.5fr 1fr 1fr 1.2fr; gap:10px; align-items:center; padding:8px 0; border-bottom:1px solid #f1f5f9;">
                        <div style="font-size:12px; font-weight:500;">
                            <span style="font-size:12px; color:#0f172a; font-weight:bold;">${inv.customer_name}</span>
                            ${actionSelect}
                        </div>
                        <div style="text-align:center; font-family:monospace;">
                            Original: <strong>${inv.original_qty}</strong>
                        </div>
                        <div style="text-align:center;">
                            <input type="number" step="1" min="0" value="${inv.quantity}" 
                                   oninput="updateInvoiceAllocation('${stateKey}', ${inv.invoice_id}, this.value)" 
                                   ${isReadOnly ? 'disabled' : ''}
                                   style="width:70px; padding:4px 8px; border:1px solid #cbd5e1; border-radius:4px; text-align:center; font-weight:bold; font-family:monospace;" />
                        </div>
                        <div style="text-align:right;">
                            <span style="font-size:11px; color:#64748b;">Rs. ${(inv.quantity * inv.unit_price).toFixed(2)}</span>
                        </div>
                    </div>
                `;
            });

            if (item.invoices.length === 0) {
                invoiceRows = `<p style="color:#64748b; font-size:12px; margin:10px 0; font-style:italic;">No invoices contain this product on this route.</p>`;
            }

            const unbalancedVal = item.final_loaded_qty - currentTotal;
            const panelStatus = (Math.abs(unbalancedVal) < 0.01)
                ? `<span style="color:#16a34a; font-weight:bold; display:inline-flex; align-items:center; gap:4px;"><i class="ph ph-check-circle" style="font-size:14px;"></i> Balanced</span>`
                : `<span style="color:#dc2626; font-weight:bold; display:inline-flex; align-items:center; gap:4px;"><i class="ph ph-warning" style="font-size:14px;"></i> Unbalanced (${unbalancedVal > 0 ? '+' : ''}${unbalancedVal.toFixed(1)} pcs)</span>`;

            let autoDistBtn = isReadOnly ? '' : `
                <button onclick="autoDistributeVariance('${stateKey}')" style="padding:4px 10px; background:#3b82f6; color:#fff; border:none; border-radius:4px; font-size:11px; font-weight:bold; cursor:pointer; margin-right:10px; display:inline-flex; align-items:center; gap:4px;"><i class="ph ph-lightning"></i> Auto-Distribute</button>
            `;

            reconciliationPanels += `
                <div style="border:1px solid #cbd5e1; border-radius:8px; padding:15px; margin-bottom:20px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; border-bottom:1px solid #e2e8f0; padding-bottom:8px;">
                        <div>
                            <strong style="font-size:13px; color:#0f172a;"><i class="ph ph-wrench"></i> Reconcile: ${item.item_name}</strong><br>
                            ${subBadge ? subBadge + '<br>' : ''}
                            <span style="font-size:11px; color:#475569;">Variance: <strong>${item.variance > 0 ? '+' : ''}${item.variance}</strong> | Required: <strong>${item.required_qty}</strong> | Final Loaded: <strong>${item.final_loaded_qty}</strong></span>
                        </div>
                        <div style="text-align:right;">
                            ${autoDistBtn}
                            ${panelStatus}
                        </div>
                    </div>
                    <div>
                        ${invoiceRows}
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-top:10px; font-size:11px; color:#475569; background:#f8fafc; padding:8px; border-radius:4px;">
                        <span>Allocated: <strong style="font-family:monospace; font-size:12px;">${currentTotal.toFixed(1)}</strong></span>
                        <span>Remaining to Allocate: <strong style="font-family:monospace; font-size:12px; color:${unbalancedVal !== 0 ? '#dc2626' : '#16a34a'};">${unbalancedVal.toFixed(1)}</strong></span>
                    </div>
                </div>
            `;
        });

        if (!hasAnyVariance) {
            reconciliationPanels = `
                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:20px; text-align:center; color:#166534; font-weight:bold; margin-bottom:20px; display:flex; align-items:center; justify-content:center; gap:8px;">
                    <i class="ph ph-check-circle" style="font-size:16px;"></i> All items are fully balanced! No billing adjustments required.
                </div>
            `;
        }

        // --- CONSTRUCT THE HTML: Reconciliation Engine at the Top, Table and totals at the bottom ---
        
        // 1. Bill Reconciliation Engine & Submit/Approve Button
        html += `
            <h5 style="margin:0 0 10px 0; font-size:13px; color:#475569; text-transform:uppercase; font-weight:bold; display:flex; align-items:center; gap:6px;"><i class="ph ph-scales"></i> Bill Reconciliation Engine</h5>
            ${reconciliationPanels}
        `;

        if (!isReadOnly) {
            html += `
                <div style="text-align:right; margin-top:10px; margin-bottom:25px; display:flex; justify-content:flex-end; gap:10px;">
            `;
            if (hasUnbalanced) {
                html += `
                    <button id="btnForceSubmitVarianceAdjustments" onclick="submitVarianceAdjustments(true)" 
                            style="padding:10px 20px; background:#b91c1c; color:#fff; border:none; border-radius:6px; font-weight:bold; font-size:13px; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                        <i class="ph ph-warning-octagon"></i> Force Approve (Bypass Variance)
                    </button>
                `;
            }
            html += `
                    <button id="btnSubmitVarianceAdjustments" onclick="submitVarianceAdjustments(false)" 
                            ${hasUnbalanced ? 'disabled' : ''}
                            style="padding:10px 20px; background:#2e7d32; color:#fff; border:none; border-radius:6px; font-weight:bold; font-size:13px; cursor:${hasUnbalanced ? 'not-allowed' : 'pointer'}; opacity:${hasUnbalanced ? 0.5 : 1}; display:inline-flex; align-items:center; gap:6px;">
                        <i class="ph ph-scales"></i> Approve & Apply Billing Adjustments
                    </button>
                </div>
            `;
        }

        // 2. Shortages / Overages Total Cards
        html += `
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:20px;">
                <div style="background:#fee2e2; border:1px solid #fca5a5; border-radius:6px; padding:12px; text-align:center; color:#991b1b;">
                    <span>Shortages to Reconcile</span><br><strong style="font-size:16px;">${totalShortages} pcs</strong>
                </div>
                <div style="background:#fff3e0; border:1px solid #ffe0b2; border-radius:6px; padding:12px; text-align:center; color:#e65100;">
                    <span>Overages to Reconcile</span><br><strong style="font-size:16px;">${totalOverages} pcs</strong>
                </div>
            </div>
            
            <h5 style="margin:0 0 10px 0; font-size:13px; color:#475569; text-transform:uppercase; font-weight:bold; display:flex; align-items:center; gap:6px;"><i class="ph ph-package"></i> Product Loading Variances</h5>
            <table class="data-table" style="margin-bottom:25px; border:1px solid #e2e8f0; border-radius:6px; overflow:hidden;">
                <thead>
                    <tr style="background:#f1f5f9;">
                        <th style="padding:10px; text-align:left;">Product Name</th>
                        <th style="padding:10px; text-align:center;">Required</th>
                        <th style="padding:10px; text-align:center;">Pre-Loaded</th>
                        <th style="padding:10px; text-align:center; background:#e2e8f0;">Final Loaded</th>
                        <th style="padding:10px; text-align:center;">Variance</th>
                        <th style="padding:10px; text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${tableRows}
                </tbody>
            </table>
        `;

        box.innerHTML = html;
    }

    function updateRemoveCompletelyChoice(stateKey, invoiceId, value) {
        const item = currentVarianceState[stateKey];
        if (item) {
            const inv = item.invoices.find(i => i.invoice_id === invoiceId);
            if (inv) {
                inv.remove_completely = parseInt(value);
            }
        }
        renderVarianceReconciliation();
    }

    function applyProductSubstitution(subId) {
        const choiceSelect = document.getElementById('pricing_choice_' + subId);
        const choice = choiceSelect ? choiceSelect.value : 'replacement';

        if (!confirm('Are you sure you want to apply this product substitution to the bills? This will modify the invoice items list, adjust stock, and update the pricing.')) {
            return;
        }

        fetchSecure('<?= APP_URL ?>/RepTracking/api_apply_substitution', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                substitution_id: subId,
                pricing_choice: choice
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                onRouteDataChanged();
                switchRouteTab(5);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('An unexpected error occurred.');
        });
    }

    function updateInvoiceAllocation(stateKey, invoiceId, value) {
        let qty = parseFloat(value);
        if (isNaN(qty) || qty < 0) {
            qty = 0;
        }
        
        const item = currentVarianceState[stateKey];
        if (item) {
            const inv = item.invoices.find(i => i.invoice_id === invoiceId);
            if (inv) {
                inv.quantity = qty;
                inv.remove_completely = (qty === 0 ? 1 : 0);
            }
        }
        renderVarianceReconciliation();
    }

    function autoDistributeVariance(stateKey) {
        const item = currentVarianceState[stateKey];
        if (!item || item.invoices.length === 0) return;

        let targetTotal = item.final_loaded_qty;
        let originalTotal = item.required_qty;
        let diff = targetTotal - originalTotal;

        if (diff === 0) {
            item.invoices.forEach(inv => {
                inv.quantity = inv.original_qty;
                inv.remove_completely = (inv.quantity === 0 ? 1 : 0);
            });
        } else if (diff < 0) {
            let shortageToDeduct = Math.abs(diff);
            item.invoices.forEach(inv => {
                if (shortageToDeduct <= 0) {
                    inv.remove_completely = (inv.quantity === 0 ? 1 : 0);
                    return;
                }
                if (inv.quantity >= shortageToDeduct) {
                    inv.quantity -= shortageToDeduct;
                    shortageToDeduct = 0;
                } else {
                    shortageToDeduct -= inv.quantity;
                    inv.quantity = 0;
                }
                inv.remove_completely = (inv.quantity === 0 ? 1 : 0);
            });
        } else {
            item.invoices[0].quantity += diff;
            item.invoices[0].remove_completely = (item.invoices[0].quantity === 0 ? 1 : 0);
            // Ensure other invoices also have correct remove_completely
            for (let i = 1; i < item.invoices.length; i++) {
                item.invoices[i].remove_completely = (item.invoices[i].quantity === 0 ? 1 : 0);
            }
        }

        renderVarianceReconciliation();
    }

    function submitVarianceAdjustments(force = false) {
        if (!currentRouteId) return;

        const adjustments = [];
        Object.values(currentVarianceState).forEach(item => {
            if (item.variance === 0) return;
            
            const invoiceAdjustments = item.invoices.map(inv => ({
                invoice_id: inv.invoice_id,
                new_qty: inv.quantity,
                remove_completely: inv.remove_completely ? 1 : 0
            }));

            adjustments.push({
                item_id: item.item_id,
                variation_option_id: item.variation_option_id || null,
                invoice_adjustments: invoiceAdjustments
            });
        });

        const confirmMsg = force 
            ? 'Are you sure you want to FORCE approve these variance adjustments, bypassing the allocation balance validation?' 
            : 'Are you sure you want to approve these variance adjustments and update invoice billing? This action will modify invoice line quantities.';

        if (adjustments.length > 0 && !confirm(confirmMsg)) {
            return;
        }

        const payload = {
            route_id: currentRouteId,
            adjustments: adjustments,
            force: force ? 1 : 0
        };

        fetchSecure('<?= APP_URL ?>/RepTracking/api_adjust_variance_billing', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                onRouteDataChanged();
                switchRouteTab(5);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('An unexpected error occurred during submission.');
        });
    }

    function printLoadingSheet(type) {
        if (!currentRouteId) return;
        window.open('<?= APP_URL ?>/RepTracking/print_loading/' + currentRouteId + '?type=' + type, '_blank');
    }

    function loadDispatchStage(routeId) {
        const formView = document.getElementById('adjDeliveryFormView');
        if (!formView) return;

        const isReadOnly = (currentRouteStatus === 'Completed' || currentRouteStatus === 'Finalized');

        // Form is always visible
        formView.style.display = 'block';

        const rdata = document.getElementById('route_data_' + routeId);
        const delId = rdata ? rdata.getAttribute('data-delivery-id') : null;

        const statusBanner = document.getElementById('adjDeliveryStatusBanner');
        const statusId = document.getElementById('adjDeliveryStatusId');

        // Load outstanding bills and pre-check
        loadOutstandingBillsChecklist(routeId, delId);

        if (!delId || delId === '0' || delId === '') {
            // Not arranged yet
            if (statusBanner) statusBanner.style.display = 'none';
            
            // Set default date if blank
            const dateInput = document.getElementById('adjDaDate');
            if (dateInput && !dateInput.value) {
                dateInput.value = new Date().toISOString().split('T')[0];
            }
            
            // Reset dropdowns
            document.getElementById('adjDaVehicle').value = '';
            document.getElementById('adjDaDriver').value = '';
            document.getElementById('adjDaPartner').value = '';
        } else {
            // Already arranged
            if (statusBanner) {
                statusBanner.style.display = 'flex';
                statusId.innerText = '#' + delId;
            }
            
            fetchSecure('<?= APP_URL ?>/RepTracking/api_get_delivery_details/' + delId)
                .then(res => res.json())
                .then(dData => {
                    if (dData.delivery) {
                        if (document.getElementById('adjDaDate')) document.getElementById('adjDaDate').value = dData.delivery.delivery_date || '';
                        if (document.getElementById('adjDaVehicle')) document.getElementById('adjDaVehicle').value = dData.delivery.vehicle_number || '';
                        if (document.getElementById('adjDaDriver')) document.getElementById('adjDaDriver').value = dData.delivery.driver_name || '';
                        if (document.getElementById('adjDaPartner')) document.getElementById('adjDaPartner').value = dData.delivery.partner_name || '';
                    }
                });
        }
    }

    function loadDeliveryLiveStage(routeId) {
        const summaryCards = document.getElementById('deliveryTabSummaryCards');
        const tbody = document.getElementById('deliveryTabInvoicesTbody');
        if (!summaryCards || !tbody) return;

        summaryCards.innerHTML = '<div style="grid-column: span 4; text-align:center; padding:10px;">Loading performance summary... </div>';
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Loading customer dispatches... </td></tr>';

        const rdata = document.getElementById('route_data_' + routeId);
        const delId = rdata ? rdata.getAttribute('data-delivery-id') : null;

        if (!delId || delId === '0' || delId === '') {
            summaryCards.innerHTML = '<div style="grid-column: span 4; text-align:center; padding:10px; color:#888;">Delivery has not been arranged/dispatched yet.</div>';
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#888;">No dispatch data available.</td></tr>';
            return;
        }

        fetchSecure('<?= APP_URL ?>/RepTracking/api_get_delivery_details/' + delId)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success' || !data.delivery) {
                    summaryCards.innerHTML = '<div style="grid-column: span 4; text-align:center; color:red;">Error loading delivery.</div>';
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:red;">Failed to load details.</td></tr>';
                    return;
                }

                const invoices = data.invoices || [];
                const totalInvoices = invoices.length;
                const delivered = invoices.filter(inv => inv.delivery_status === 'Delivered').length;
                const pending = totalInvoices - delivered;
                const collections = parseFloat(data.balancing.total_payments || 0);

                summaryCards.innerHTML = `
                    <div style="background:#f0f9ff; border:1px solid #bae6fd; padding:12px; border-radius:6px; text-align:center;">
                        <span style="font-size:11px; color:#0369a1; font-weight:bold;">Total Invoices</span><br>
                        <strong style="font-size:15px; color:#0f172a;">${totalInvoices}</strong>
                    </div>
                    <div style="background:#f0fdf4; border:1px solid #bbf7d0; padding:12px; border-radius:6px; text-align:center;">
                        <span style="font-size:11px; color:#166534; font-weight:bold;">Delivered</span><br>
                        <strong style="font-size:15px; color:#166534;">${delivered}</strong>
                    </div>
                    <div style="background:#fff7ed; border:1px solid #ffedd5; padding:12px; border-radius:6px; text-align:center;">
                        <span style="font-size:11px; color:#c2410c; font-weight:bold;">Pending Visit</span><br>
                        <strong style="font-size:15px; color:#c2410c;">${pending}</strong>
                    </div>
                    <div style="background:#f0fdf4; border:1px solid #bbf7d0; padding:12px; border-radius:6px; text-align:center;">
                        <span style="font-size:11px; color:#166534; font-weight:bold;">Collected Amount</span><br>
                        <strong style="font-size:15px; color:#166534;">Rs ${collections.toLocaleString('en-US', {minimumFractionDigits: 2})}</strong>
                    </div>
                `;

                tbody.innerHTML = '';
                if (invoices.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#888;">No invoices dispatched.</td></tr>';
                    return;
                }

                invoices.forEach(inv => {
                    let dColor = '#d05d00';
                    if (inv.delivery_status === 'Delivered') dColor = '#2e7d32';
                    else if (inv.delivery_status === 'Cancelled') dColor = '#ef4444';
                    else if (inv.delivery_status === 'Postponed') dColor = '#6b7280';

                    let pColor = inv.status === 'Paid' ? '#2e7d32' : '#d05d00';
                    
                    let actionHtml = '';
                    if (currentRouteStatus === 'Completed' || currentRouteStatus === 'Finalized') {
                        actionHtml = `<span style="color:#888; font-size:11px; font-weight:bold;">Closed</span>`;
                    } else if (inv.delivery_status !== 'Pending') {
                        actionHtml = `<span style="color:#6b7280; font-size:11px; font-weight:bold;">Processed</span>`;
                    } else {
                        actionHtml = `
                            <button onclick="openServerDeliveryProcessModal(${inv.id}, ${inv.customer_id}, '${inv.invoice_number}', '${inv.customer_name.replace(/'/g, "\\'")}', ${inv.true_grand_total})" 
                                    class="btn-premium primary" 
                                    style="padding:4px 8px; font-size:11px; display:inline-flex; align-items:center; gap:4px; font-weight:bold; cursor:pointer;">
                                <i class="ph ph-gear"></i> Process Visit
                            </button>
                        `;
                    }

                    let statusSelectHtml = '';
                    const isReadOnly = (currentRouteStatus === 'Completed' || currentRouteStatus === 'Finalized');
                    if (isReadOnly) {
                        statusSelectHtml = `<span style="color:${dColor}; font-weight:bold;">${inv.delivery_status || 'Pending'}</span>`;
                    } else {
                        statusSelectHtml = `
                            <select onchange="updateSingleInvoiceDeliveryStatus(${inv.id}, ${inv.customer_id}, this.value)" 
                                    style="padding: 4px 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: bold; color: ${dColor}; background: #fff; outline: none; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-xs);">
                                <option value="Pending" ${inv.delivery_status === 'Pending' ? 'selected' : ''} style="color:#d05d00;">Pending</option>
                                <option value="Delivered" ${inv.delivery_status === 'Delivered' ? 'selected' : ''} style="color:#2e7d32;">Delivered</option>
                                <option value="Cancelled" ${inv.delivery_status === 'Cancelled' ? 'selected' : ''} style="color:#ef4444;">Cancelled</option>
                                <option value="Postponed" ${inv.delivery_status === 'Postponed' ? 'selected' : ''} style="color:#6b7280;">Postponed</option>
                            </select>
                        `;
                    }

                    tbody.innerHTML += `
                        <tr>
                            <td><strong>${inv.customer_name}</strong></td>
                            <td style="font-weight:bold; color:var(--primary);">${inv.invoice_number}</td>
                            <td style="text-align:right; font-family:monospace; font-weight:bold;">Rs ${parseFloat(inv.true_grand_total).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                            <td style="text-align:center;">${statusSelectHtml}</td>
                            <td style="text-align:center; color:${pColor}; font-weight:bold;">${inv.status}</td>
                            <td style="text-align:center;">${actionHtml}</td>
                        </tr>
                    `;
                });
            });
    }

    function openServerDeliveryProcessModal(invoiceId, customerId, invoiceNumber, customerName, grandTotal) {
        document.getElementById('sdpInvoiceId').value = invoiceId;
        document.getElementById('sdpInvoiceId').setAttribute('data-grand-total', grandTotal);
        document.getElementById('sdpCustomerId').value = customerId;
        document.getElementById('sdpCustomerName').innerText = customerName;
        document.getElementById('sdpInvoiceNumber').innerText = invoiceNumber;
        document.getElementById('sdpCashAmount').value = '0.00';
        document.getElementById('sdpBankAmount').value = '0.00';
        document.getElementById('sdpChequesContainer').innerHTML = '';
        document.getElementById('sdpItemsTbody').innerHTML = '<tr><td colspan="3" style="text-align:center;">Loading items... </td></tr>';
        
        document.getElementById('sdpInvoiceTotal').innerText = 'Rs ' + parseFloat(grandTotal).toLocaleString('en-US', {minimumFractionDigits: 2});
        updateSdpBalance();
        
        if (document.getElementById('sdpItemSearch')) {
            document.getElementById('sdpItemSearch').value = '';
        }
        
        document.getElementById('serverDeliveryProcessModal').style.display = 'flex';
        
        fetchSecure('<?= APP_URL ?>/RepTracking/api_get_invoice_for_delivery/' + invoiceId)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    let statusVal = data.invoice.delivery_status || 'Pending';
                    if (statusVal === 'Pending') statusVal = 'Delivered';
                    document.getElementById('sdpDeliveryStatus').value = statusVal;
                    
                    const arrears = parseFloat(data.arrears || 0);
                    document.getElementById('sdpOutstandingArrears').innerText = 'Rs ' + arrears.toLocaleString('en-US', {minimumFractionDigits: 2});
                    
                    // Calculate tax percentage
                    let taxPercentage = 0;
                    const subTotal = parseFloat(data.invoice.total_amount || 0);
                    const globalDiscVal = parseFloat(data.invoice.global_discount_val || 0);
                    const globalDiscType = data.invoice.global_discount_type || 'Rs';
                    const globalDisc = (globalDiscType === '%') ? (subTotal * globalDiscVal / 100) : globalDiscVal;
                    const netSub = Math.max(0, subTotal - globalDisc);
                    if (netSub > 0) {
                        taxPercentage = parseFloat(data.invoice.tax_amount || 0) / netSub;
                    }
                    
                    const modal = document.getElementById('serverDeliveryProcessModal');
                    modal.setAttribute('data-global-discount-val', globalDiscVal);
                    modal.setAttribute('data-global-discount-type', globalDiscType);
                    modal.setAttribute('data-tax-percentage', taxPercentage);
                    
                    let tbody = document.getElementById('sdpItemsTbody');
                    tbody.innerHTML = '';
                    
                    if (!data.items || data.items.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; color:#888;">No items in this invoice.</td></tr>';
                    } else {
                        data.items.forEach(item => {
                            tbody.innerHTML += `
                                <tr data-item-id="${item.id}">
                                    <td><strong>${item.description}</strong></td>
                                    <td style="text-align:right; font-family:monospace;">${parseInt(item.loaded_quantity)}</td>
                                    <td style="text-align:right;">
                                        <input type="number" step="1" min="0" max="${parseInt(item.loaded_quantity)}" class="sdp-delivered-qty" 
                                               value="${parseInt(item.quantity)}" 
                                               data-price="${parseFloat(item.unit_price || 0)}"
                                               data-discount-type="${item.discount_type || 'Rs'}"
                                               data-discount-val="${parseFloat(item.discount_value || 0)}"
                                               oninput="recalculateSdpInvoiceTotal()"
                                               style="width: 80px; text-align: right; padding: 4px 8px; border: 1px solid #cbd5e1; border-radius: 4px;" />
                                    </td>
                                </tr>
                            `;
                        });
                        recalculateSdpInvoiceTotal();
                    }
                } else {
                    alert('Error: ' + data.message);
                    closeServerDeliveryProcessModal();
                }
            })
            .catch(err => {
                console.error(err);
                alert('Failed to load invoice items.');
                closeServerDeliveryProcessModal();
            });
    }

    function filterSdpItems() {
        const query = document.getElementById('sdpItemSearch').value.toLowerCase().trim();
        const rows = document.querySelectorAll('#sdpItemsTbody tr');
        rows.forEach(row => {
            const descTd = row.querySelector('td:first-child');
            if (descTd) {
                const desc = descTd.innerText.toLowerCase();
                if (desc.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    function updateSdpBalance() {
        const grandTotal = parseFloat(document.getElementById('sdpInvoiceId').getAttribute('data-grand-total') || 0);
        const cash = parseFloat(document.getElementById('sdpCashAmount').value) || 0;
        const bank = parseFloat(document.getElementById('sdpBankAmount').value) || 0;
        
        let chequeTotal = 0;
        document.querySelectorAll('.sdp-ch-amount').forEach(input => {
            chequeTotal += parseFloat(input.value) || 0;
        });
        
        const totalCollected = cash + bank + chequeTotal;
        const balance = grandTotal - totalCollected;
        
        document.getElementById('sdpTotalCollected').innerText = 'Rs ' + totalCollected.toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('sdpRemainingBalance').innerText = 'Rs ' + balance.toLocaleString('en-US', {minimumFractionDigits: 2});
        
        if (balance <= 0) {
            document.getElementById('sdpRemainingBalance').style.color = '#2e7d32';
        } else {
            document.getElementById('sdpRemainingBalance').style.color = '#c62828';
        }
    }

    function recalculateSdpInvoiceTotal() {
        const modal = document.getElementById('serverDeliveryProcessModal');
        const globalDiscVal = parseFloat(modal.getAttribute('data-global-discount-val') || 0);
        const globalDiscType = modal.getAttribute('data-global-discount-type') || 'Rs';
        const taxPercentage = parseFloat(modal.getAttribute('data-tax-percentage') || 0);
        
        let subTotal = 0;
        document.querySelectorAll('#sdpItemsTbody tr').forEach(row => {
            const qtyInput = row.querySelector('.sdp-delivered-qty');
            if (qtyInput) {
                const qty = parseInt(qtyInput.value) || 0;
                const price = parseFloat(qtyInput.getAttribute('data-price') || 0);
                const discVal = parseFloat(qtyInput.getAttribute('data-discount-val') || 0);
                const discType = qtyInput.getAttribute('data-discount-type') || 'Rs';
                
                const rowGross = qty * price;
                const rowDisc = (discType === '%') ? (rowGross * discVal / 100) : discVal;
                const rowTotal = Math.max(0, rowGross - rowDisc);
                subTotal += rowTotal;
            }
        });
        
        const globalDisc = (globalDiscType === '%') ? (subTotal * globalDiscVal / 100) : globalDiscVal;
        const netSub = Math.max(0, subTotal - globalDisc);
        const taxAmount = netSub * taxPercentage;
        const newGrandTotal = netSub + taxAmount;
        
        document.getElementById('sdpInvoiceId').setAttribute('data-grand-total', newGrandTotal);
        document.getElementById('sdpInvoiceTotal').innerText = 'Rs ' + newGrandTotal.toLocaleString('en-US', {minimumFractionDigits: 2});
        
        updateSdpBalance();
    }

    function closeServerDeliveryProcessModal() {
        document.getElementById('serverDeliveryProcessModal').style.display = 'none';
    }

    function addSdpChequeRow() {
        const container = document.getElementById('sdpChequesContainer');
        const row = document.createElement('div');
        row.className = 'sdp-cheque-row';
        row.style.display = 'grid';
        row.style.gridTemplateColumns = '1.5fr 1fr 1.2fr 1fr 40px';
        row.style.gap = '10px';
        row.style.alignItems = 'center';
        row.style.background = '#f8fafc';
        row.style.padding = '10px';
        row.style.borderRadius = '6px';
        row.style.border = '1px solid #e2e8f0';
        
        row.innerHTML = `
            <input type="text" placeholder="Bank Name" class="sdp-ch-bank" style="padding:6px; border:1px solid #ccc; border-radius:4px; font-size:12px; width:100%;" required />
            <input type="text" placeholder="Cheque #" class="sdp-ch-number" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '');" style="padding:6px; border:1px solid #ccc; border-radius:4px; font-size:12px; width:100%;" required />
            <input type="date" class="sdp-ch-date" style="padding:6px; border:1px solid #ccc; border-radius:4px; font-size:12px; width:100%;" required />
            <input type="number" step="0.01" min="0" placeholder="Amount" class="sdp-ch-amount" oninput="updateSdpBalance()" style="padding:6px; border:1px solid #ccc; border-radius:4px; font-size:12px; width:100%;" required />
            <button type="button" onclick="this.closest('.sdp-cheque-row').remove(); updateSdpBalance();" style="background:none; border:none; color:#ef4444; font-size:18px; cursor:pointer; display:flex; align-items:center; justify-content:center;"><i class="ph ph-trash"></i></button>
        `;
        container.appendChild(row);
    }

    function submitServerDeliveryProcess() {
        const routeId = currentRouteId;
        const customerId = document.getElementById('sdpCustomerId').value;
        const invoiceId = document.getElementById('sdpInvoiceId').value;
        
        let deliveryStatus = document.getElementById('sdpDeliveryStatus').value;
        if (deliveryStatus === 'Pending') {
            deliveryStatus = 'Delivered';
            document.getElementById('sdpDeliveryStatus').value = 'Delivered';
        }
        
        // 1. Gather invoice items updates and validate range
        const items = [];
        let qtyValidationFailed = false;
        let qtyValidationMsg = '';
        document.querySelectorAll('#sdpItemsTbody tr').forEach(row => {
            const itemId = row.getAttribute('data-item-id');
            const qtyInput = row.querySelector('.sdp-delivered-qty');
            if (itemId && qtyInput) {
                const val = parseInt(qtyInput.value) || 0;
                const maxVal = parseInt(qtyInput.getAttribute('max')) || 0;
                if (val < 0) {
                    qtyValidationFailed = true;
                    qtyValidationMsg = 'Delivered quantity cannot be negative.';
                }
                if (val > maxVal) {
                    qtyValidationFailed = true;
                    qtyValidationMsg = 'Delivered quantity cannot be greater than loaded quantity (' + maxVal + ').';
                }
                items.push({
                    invoice_item_id: parseInt(itemId),
                    delivered_qty: val
                });
            }
        });

        if (qtyValidationFailed) {
            alert(qtyValidationMsg);
            return;
        }
        
        // 2. Gather payments & collections
        const cash = parseFloat(document.getElementById('sdpCashAmount').value) || 0;
        const bank = parseFloat(document.getElementById('sdpBankAmount').value) || 0;
        
        const cheques = [];
        let chequeValidationFailed = false;
        document.querySelectorAll('.sdp-cheque-row').forEach(row => {
            const bankName = row.querySelector('.sdp-ch-bank').value.trim();
            const chNum = row.querySelector('.sdp-ch-number').value.trim();
            const chDate = row.querySelector('.sdp-ch-date').value;
            const chAmt = parseFloat(row.querySelector('.sdp-ch-amount').value) || 0;
            
            if (!bankName || !chNum || !chDate || chAmt <= 0) {
                chequeValidationFailed = true;
            }
            
            cheques.push({
                bank: bankName,
                number: chNum,
                date: chDate,
                amount: chAmt
            });
        });
        
        if (chequeValidationFailed) {
            alert('Please fill out all fields in the added cheque rows with valid values.');
            return;
        }
        
        // Confirm action
        if (!confirm('Are you sure you want to process this visit and save changes? This will modify the delivery status, adjust quantities, and record payments.')) {
            return;
        }
        
        const payload = {
            route_id: parseInt(routeId),
            customer_id: parseInt(customerId),
            deliveries: [
                {
                    invoice_id: parseInt(invoiceId),
                    delivery_status: deliveryStatus,
                    items: items
                }
            ],
            collections: {
                cash: cash,
                bank: bank,
                cheques: cheques
            }
        };
        
        fetchSecure('<?= APP_URL ?>/RepTracking/api_process_delivery_visit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                closeServerDeliveryProcessModal();
                loadDeliveryLiveStage(routeId);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('An unexpected error occurred during submission.');
        });
    }

    function applyDefensiveGuard(delId, guardId, contentId) {
        const guardEl = document.getElementById(guardId);
        const contentEl = document.getElementById(contentId);
        if (!guardEl || !contentEl) return false;

        let isBlocked = false;
        let title = '';
        let desc = '';

        if (!delId || delId === '0' || delId === '') {
            isBlocked = true;
            title = 'Delivery Data Incomplete';
            desc = 'Reconciliation and postings are unavailable because delivery has not been arranged for this route yet.';
        } else if (currentRouteStatus !== 'Finalizing' && currentRouteStatus !== 'Completed' && currentRouteStatus !== 'Finalized') {
            isBlocked = true;
            title = 'Preview Not Available';
            desc = 'Reconciliation and GL postings can only be performed once the route delivery has completed execution.';
        }

        if (isBlocked) {
            guardEl.innerHTML = `
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 45px 20px; text-align: center; max-width: 580px; margin: 40px auto; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="width: 60px; height: 60px; background: #fffbeb; color: #d97706; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; font-size: 28px;">
                        <i class="ph ph-warning-circle"></i>
                    </div>
                    <h4 style="margin: 0 0 10px 0; font-size: 15px; font-weight: bold; color: #0f172a;">${title}</h4>
                    <p style="margin: 0; font-size: 12px; color: #64748b; line-height: 1.6;">${desc}</p>
                </div>
            `;
            guardEl.style.display = 'block';
            contentEl.style.display = 'none';
            return true;
        } else {
            guardEl.style.display = 'none';
            contentEl.style.display = 'block';
            return false;
        }
    }

    function applyPaymentsDefensiveGuard(routeId, delId) {
        const guardEl = document.getElementById('tab10GuardContainer');
        const contentEl = document.getElementById('tab10ContentContainer');
        if (!guardEl || !contentEl) return false;

        let isBlocked = false;
        let title = '';
        let desc = '';

        if (currentRouteStatus === 'Active') {
            isBlocked = true;
            title = 'Route Still Active';
            desc = 'Payments collected cannot be verified until the representative has ended the route from their app.';
        }

        if (isBlocked) {
            guardEl.innerHTML = `
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 45px 20px; text-align: center; max-width: 580px; margin: 40px auto; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="width: 60px; height: 60px; background: #fffbeb; color: #d97706; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; font-size: 28px;">
                        <i class="ph ph-warning-circle"></i>
                    </div>
                    <h4 style="margin: 0 0 10px 0; font-size: 15px; font-weight: bold; color: #0f172a;">${title}</h4>
                    <p style="margin: 0; font-size: 12px; color: #64748b; line-height: 1.6;">${desc}</p>
                </div>
            `;
            guardEl.style.display = 'block';
            contentEl.style.display = 'none';
            return true;
        } else {
            guardEl.style.display = 'none';
            contentEl.style.display = 'block';
            return false;
        }
    }

    function loadTab8Reconciliation(routeId) {
        const rdata = document.getElementById('route_data_' + routeId);
        const delId = rdata ? rdata.getAttribute('data-delivery-id') : null;

        if (applyDefensiveGuard(delId, 'tab7GuardContainer', 'tab7ContentContainer')) {
            return;
        }

        document.getElementById('reconExpectedCash').innerText = 'Rs 0.00';
        document.getElementById('reconExpectedCollections').innerText = 'Rs 0.00';
        document.getElementById('reconTotalExpectedCash').innerText = 'Rs 0.00';
        document.getElementById('reconActualCash').value = '0.00';
        document.getElementById('reconCashVariance').innerText = 'Rs 0.00';
        document.getElementById('reconAuditNotes').value = '';
        document.getElementById('reconChequesTbody').innerHTML = '<tr><td colspan="4" style="text-align:center;">Loading cheques... </td></tr>';

        const isReadOnly = (currentRouteStatus === 'Completed' || currentRouteStatus === 'Finalized');
        const saveBtn = document.getElementById('btnSaveReconciliationDraft');
        if (saveBtn) {
            saveBtn.disabled = isReadOnly;
            saveBtn.style.opacity = isReadOnly ? '0.5' : '1';
            saveBtn.style.cursor = isReadOnly ? 'not-allowed' : 'pointer';
        }
        document.getElementById('reconActualCash').disabled = isReadOnly;
        document.getElementById('reconAuditNotes').disabled = isReadOnly;

        const denList = [5000, 2000, 1000, 500, 100, 50, 20];
        denList.forEach(den => {
            document.getElementById('actQty' + den).disabled = isReadOnly;
        });
        document.getElementById('actValCoins').disabled = isReadOnly;

        fetchSecure('<?= APP_URL ?>/RepTracking/api_get_delivery_details/' + delId)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success') return;

                currentDeliveryDetails = data;
                const balancing = data.balancing;

                const expectedCashSales = parseFloat(balancing.cash_sales || 0);
                const totalExpectedCash = parseFloat(balancing.cash_collections || 0);
                const expectedCashColls = Math.max(0, totalExpectedCash - expectedCashSales);

                document.getElementById('reconExpectedCash').innerText = 'Rs ' + expectedCashSales.toLocaleString('en-US', {minimumFractionDigits: 2});
                document.getElementById('reconExpectedCollections').innerText = 'Rs ' + expectedCashColls.toLocaleString('en-US', {minimumFractionDigits: 2});
                document.getElementById('reconTotalExpectedCash').innerText = 'Rs ' + totalExpectedCash.toLocaleString('en-US', {minimumFractionDigits: 2});

                let actualCash = 0;
                let remarks = '';
                let actualDenoms = null;
                let chequeApprovals = {};
                let isReadOnly = (currentRouteStatus === 'Completed' || currentRouteStatus === 'Finalized');

                if (data.delivery && data.delivery.reconciliation_json) {
                    try {
                        const recon = JSON.parse(data.delivery.reconciliation_json);
                        actualCash = parseFloat(recon.actual_cash || 0);
                        remarks = recon.audit_remarks || '';
                        actualDenoms = recon.actual_denominations || null;
                        chequeApprovals = recon.cheque_approvals || {};
                        if (recon && (recon.actual_cash !== undefined || recon.actual_denominations)) {
                            isReadOnly = true;
                        }
                    } catch(e) {}
                }

                // Re-apply read-only locks to form elements
                const saveBtn = document.getElementById('btnSaveReconciliationDraft');
                if (saveBtn) {
                    saveBtn.disabled = isReadOnly;
                    saveBtn.style.opacity = isReadOnly ? '0.5' : '1';
                    saveBtn.style.cursor = isReadOnly ? 'not-allowed' : 'pointer';
                }
                document.getElementById('reconActualCash').disabled = isReadOnly;
                document.getElementById('reconAuditNotes').disabled = isReadOnly;
                denList.forEach(den => {
                    document.getElementById('actQty' + den).disabled = isReadOnly;
                });
                document.getElementById('actValCoins').disabled = isReadOnly;

                // Parse collector's denominations
                let colDenoms = {};
                if (data.delivery && data.delivery.cash_denominations) {
                    try {
                        colDenoms = JSON.parse(data.delivery.cash_denominations) || {};
                    } catch(e) {}
                }

                // Populate collector column UI
                denList.forEach(den => {
                    const qty = parseInt(colDenoms[den] || 0);
                    const val = qty * den;
                    document.getElementById('colQty' + den).innerText = qty;
                    document.getElementById('colVal' + den).innerText = val.toLocaleString('en-US', {minimumFractionDigits: 2});
                });
                const colCoins = parseFloat(colDenoms.coins || 0);
                document.getElementById('colValCoins').innerText = colCoins.toLocaleString('en-US', {minimumFractionDigits: 2});

                // Populate actual column UI
                // If actualDenoms is present, use it. Otherwise, default to collector's denominations.
                const denToUse = actualDenoms || colDenoms;

                denList.forEach(den => {
                    const qty = parseInt(denToUse[den] || 0);
                    document.getElementById('actQty' + den).value = qty || '';
                });
                const actCoins = parseFloat(denToUse.coins || 0);
                document.getElementById('actValCoins').value = actCoins || '';

                // Call recalculateDenominations to calculate totals and values
                recalculateDenominations();

                document.getElementById('reconAuditNotes').value = remarks;

                // Render cheques
                const chequesTbody = document.getElementById('reconChequesTbody');
                chequesTbody.innerHTML = '';
                const cheques = balancing.payments ? balancing.payments.filter(p => p.payment_method === 'Cheque') : [];
                if (cheques.length === 0) {
                    chequesTbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:#888; padding:10px;">No cheques collected.</td></tr>';
                } else {
                    cheques.forEach((ch, idx) => {
                        let isChApproved = (chequeApprovals && (chequeApprovals[ch.id] === true || chequeApprovals[String(ch.id)] === true)) || parseInt(ch.is_verified) === 1;
                        ch.is_verified = isChApproved ? 1 : 0;
                        let approveBox = `
                            <input type="checkbox" onchange="toggleReconChequeApproval(${ch.id}, this.checked)" 
                                   ${isChApproved ? 'checked' : ''} ${isReadOnly ? 'disabled' : ''} 
                                   data-payment-id="${ch.id}"
                                   style="width:16px; height:16px; cursor:${isReadOnly ? 'not-allowed' : 'pointer'};" />
                        `;
                        chequesTbody.innerHTML += `
                            <tr>
                                <td><strong>${ch.customer_name}</strong></td>
                                <td>${ch.reference || 'N/A'}</td>
                                <td style="text-align:right; font-family:monospace; font-weight:bold;">Rs ${parseFloat(ch.amount).toFixed(2)}</td>
                                <td style="text-align:center;">${approveBox}</td>
                            </tr>
                        `;
                    });
                }
            });
    }

    function recalculateDenominations() {
        const denoms = [5000, 2000, 1000, 500, 100, 50, 20];
        let total = 0;
        denoms.forEach(den => {
            const qty = parseInt(document.getElementById('actQty' + den).value || 0);
            const val = qty * den;
            document.getElementById('actVal' + den).innerText = val.toLocaleString('en-US', {minimumFractionDigits: 2});
            total += val;
        });

        const coinsVal = parseFloat(document.getElementById('actValCoins').value || 0);
        document.getElementById('actValCoinsTotal').innerText = coinsVal.toLocaleString('en-US', {minimumFractionDigits: 2});
        total += coinsVal;

        document.getElementById('reconActualCash').value = total.toFixed(2);
        calculateCashVariance();
    }

    function calculateCashVariance() {
        const expectedStr = document.getElementById('reconTotalExpectedCash').innerText.replace('Rs ', '').replace(/,/g, '');
        const expected = parseFloat(expectedStr) || 0;
        const actual = parseFloat(document.getElementById('reconActualCash').value) || 0;
        const variance = actual - expected;

        const el = document.getElementById('reconCashVariance');
        el.innerText = 'Rs ' + variance.toLocaleString('en-US', {minimumFractionDigits: 2});
        if (variance < 0) {
            el.style.color = '#c62828';
        } else if (variance > 0) {
            el.style.color = '#2e7d32';
        } else {
            el.style.color = '#000';
        }
    }

    function toggleReconChequeApproval(paymentId, checked) {
        if (!currentDeliveryDetails) return;
        const payments = currentDeliveryDetails.balancing.payments || [];
        const ch = payments.find(p => p.id === paymentId);
        if (ch) {
            ch.is_verified = checked ? 1 : 0;
        }
    }

    function saveReconciliationDraft() {
        if (!currentRouteId || !currentDeliveryDetails) return;
        
        // Verify all cheques are checked
        const chequeCheckboxes = document.querySelectorAll('#reconChequesTbody input[type="checkbox"]');
        let allChequesVerified = true;
        chequeCheckboxes.forEach(cb => {
            if (!cb.checked) {
                allChequesVerified = false;
            }
        });
        if (!allChequesVerified) {
            alert('Verification of all cheques is required before saving the reconciliation.');
            return;
        }

        const actualCash = parseFloat(document.getElementById('reconActualCash').value) || 0;
        const remarks = document.getElementById('reconAuditNotes').value;

        const chequeApprovals = {};
        const payments = currentDeliveryDetails.balancing.payments || [];
        payments.forEach(p => {
            if (p.payment_method === 'Cheque') {
                chequeApprovals[p.id] = parseInt(p.is_verified) === 1;
            }
        });

        const actualDenoms = {};
        const denoms = [5000, 2000, 1000, 500, 100, 50, 20];
        denoms.forEach(den => {
            actualDenoms[den] = parseInt(document.getElementById('actQty' + den).value || 0);
        });
        actualDenoms['coins'] = parseFloat(document.getElementById('actValCoins').value || 0);

        const reconData = {
            actual_cash: actualCash,
            audit_remarks: remarks,
            cheque_approvals: chequeApprovals,
            actual_denominations: actualDenoms
        };

        const deliveryId = currentDeliveryDetails.delivery.id;

        fetchSecure('<?= APP_URL ?>/RepTracking/api_save_reconciliation', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ delivery_id: deliveryId, reconciliation_data: reconData })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("Reconciliation draft saved successfully!");
                onRouteDataChanged();
            } else {
                alert("Error: " + data.message);
            }
        });
    }

    function loadTab9ReturnStock(routeId) {
        const rdata = document.getElementById('route_data_' + routeId);
        const delId = rdata ? rdata.getAttribute('data-delivery-id') : null;

        const tbody = document.getElementById('settleStockTableBody');
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Loading return stock counts... </td></tr>';

        const verifyStockCheck = document.getElementById('settleVerifyStock');
        if (verifyStockCheck) {
            verifyStockCheck.checked = false;
        }

        if (!delId || delId === '0' || delId === '') {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#888;">Delivery has not been arranged.</td></tr>';
            return;
        }

        const isReadOnly = (currentRouteStatus === 'Completed' || currentRouteStatus === 'Finalized');
        const saveBtn = document.getElementById('btnSaveReturnStockDraft');
        if (saveBtn) {
            saveBtn.disabled = isReadOnly;
            saveBtn.style.opacity = isReadOnly ? '0.5' : '1';
            saveBtn.style.cursor = isReadOnly ? 'not-allowed' : 'pointer';
        }
        if (verifyStockCheck) {
            verifyStockCheck.disabled = isReadOnly;
        }

        fetchSecure('<?= APP_URL ?>/RepTracking/api_get_delivery_details/' + delId)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success') return;
                currentDeliveryDetails = data;

                let savedReturnStock = null;
                if (data.delivery && data.delivery.return_stock_json) {
                    try {
                        savedReturnStock = JSON.parse(data.delivery.return_stock_json);
                    } catch(e) {}
                }

                const isReadOnly = (currentRouteStatus === 'Completed' || currentRouteStatus === 'Finalized');
                if (saveBtn) {
                    saveBtn.disabled = isReadOnly;
                    saveBtn.style.opacity = isReadOnly ? '0.5' : '1';
                    saveBtn.style.cursor = isReadOnly ? 'not-allowed' : 'pointer';
                }
                if (verifyStockCheck) {
                    verifyStockCheck.disabled = isReadOnly;
                    if (savedReturnStock) {
                        verifyStockCheck.checked = true;
                    }
                }

                tbody.innerHTML = '';
                if (!data.balancing.stock_items || data.balancing.stock_items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#888;">No stock items loaded.</td></tr>';
                } else {
                    data.balancing.stock_items.forEach(st => {
                        let expectedReturned = parseInt(st.loaded_qty) - parseInt(st.delivered_qty);
                        if (expectedReturned < 0) expectedReturned = 0;
                        
                        let actualCounted = parseInt(expectedReturned);
                        if (savedReturnStock) {
                            const savedVal = savedReturnStock.find(x => x.item_id === st.item_id && x.variation_option_id === st.variation_option_id);
                            if (savedVal) {
                                actualCounted = parseInt(savedVal.actual_returned_qty);
                            }
                        }

                        tbody.innerHTML += `
                            <tr>
                                <td><strong>${st.item_name}</strong></td>
                                <td style="text-align:center; font-weight:bold;">${parseInt(st.loaded_qty)}</td>
                                <td style="text-align:center; color:#2e7d32; font-weight:bold;">${parseInt(st.delivered_qty)}</td>
                                <td style="text-align:center; font-weight:bold; font-family:monospace; background:#fafafa;">${expectedReturned}</td>
                                <td style="text-align:right;">
                                    <input type="number" class="actual-returned-input" 
                                           data-name="${st.item_name}" data-item-id="${st.item_id}" data-var-id="${st.variation_option_id || 0}"
                                           data-loaded="${st.loaded_qty}" data-delivered="${st.delivered_qty}" 
                                           value="${actualCounted}" min="0" ${isReadOnly ? 'disabled' : ''}
                                           style="width:80px; text-align:right; padding:4px; font-family:monospace; font-weight:bold;" />
                                </td>
                            </tr>
                        `;
                    });
                }

                checkSettleVerification();
            });
    }

    function saveReturnStockDraft() {
        if (!currentRouteId || !currentDeliveryDetails) {
            console.log("saveReturnStockDraft aborted: missing currentRouteId or currentDeliveryDetails", {currentRouteId, currentDeliveryDetails});
            return;
        }

        if (!confirm("Save return stock draft?")) {
            return;
        }

        const returnedItems = [];
        document.querySelectorAll('.actual-returned-input').forEach(input => {
            returnedItems.push({
                item_name: input.getAttribute('data-name'),
                item_id: parseInt(input.getAttribute('data-item-id') || 0),
                variation_option_id: parseInt(input.getAttribute('data-var-id') || 0),
                loaded_qty: parseInt(input.getAttribute('data-loaded') || 0),
                delivered_qty: parseInt(input.getAttribute('data-delivered') || 0),
                actual_returned_qty: parseInt(input.value || 0)
            });
        });

        const deliveryId = currentDeliveryDetails.delivery.id;
        const payload = { delivery_id: deliveryId, return_stock_data: returnedItems };
        console.log("[ReturnStock] Submitting Save Draft. Payload:", payload);

        fetchSecure('<?= APP_URL ?>/RepTracking/api_save_return_stock', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => {
            console.log("[ReturnStock] Server response status:", res.status);
            return res.text();
        })
        .then(text => {
            console.log("[ReturnStock] Server raw body:", text);
            let data;
            try {
                data = JSON.parse(text);
            } catch(e) {
                console.error("[ReturnStock] Failed to parse response as JSON. Error:", e);
                alert("Server returned invalid response. Please check the browser console.");
                return;
            }

            console.log("[ReturnStock] Parsed response data:", data);
            if (data.debug_logs && Array.isArray(data.debug_logs)) {
                console.group("[ReturnStock] Backend Debug Execution Trace:");
                data.debug_logs.forEach(logLine => console.log(logLine));
                console.groupEnd();
            }

            if (data.status === 'success') {
                console.log("[ReturnStock] Save draft success. Updating local state and UI.");
                alert("Return stock verified and saved successfully!");
                // Update local delivery details to avoid page-reload requirements
                if (currentDeliveryDetails && currentDeliveryDetails.delivery) {
                    currentDeliveryDetails.delivery.return_stock_json = JSON.stringify(returnedItems);
                }
                // Synchronize the checkbox
                const verifyStockCheck = document.getElementById('settleVerifyStock');
                if (verifyStockCheck) {
                    verifyStockCheck.checked = true;
                }
                checkSettleVerification();
                onRouteDataChanged();
            } else {
                console.warn("[ReturnStock] Save draft failed. Server message:", data.message);
                alert("Error: " + data.message);
            }
        })
        .catch(err => {
            console.error("[ReturnStock] Fetch error:", err);
            alert("An unexpected network error occurred. Please check the browser console.");
        });
    }

    function loadTab10Accounting(routeId) {
        const rdata = document.getElementById('route_data_' + routeId);
        const delId = rdata ? rdata.getAttribute('data-delivery-id') : null;

        if (applyPaymentsDefensiveGuard(routeId, delId)) {
            return;
        }

        const colContainer = document.getElementById('settleDeCollectionsContainer');
        const salesContainer = document.getElementById('settleDeSalesContainer');
        
        colContainer.innerHTML = '<p style="text-align:center; color:#888;">Loading account mappings... </p>';
        salesContainer.innerHTML = '';

        document.getElementById('settleDaVehicle').value = '';
        document.getElementById('settleDaDriver').value = '';
        document.getElementById('settleDaPartner').value = '';

        const isReadOnly = (currentRouteStatus === 'Completed' || currentRouteStatus === 'Finalized');
        const saveBtn = document.getElementById('btnSaveAccountingDraft');
        if (saveBtn) {
            saveBtn.disabled = isReadOnly;
            saveBtn.style.opacity = isReadOnly ? '0.5' : '1';
            saveBtn.style.cursor = isReadOnly ? 'not-allowed' : 'pointer';
        }
        document.getElementById('settleDaVehicle').disabled = isReadOnly;
        document.getElementById('settleDaDriver').disabled = isReadOnly;
        document.getElementById('settleDaPartner').disabled = isReadOnly;

        fetchSecure('<?= APP_URL ?>/RepTracking/api_get_delivery_details/' + (delId || 0) + '?route_id=' + routeId)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success') return;
                currentDeliveryDetails = data;

                if (data.delivery) {
                    document.getElementById('settleDaVehicle').value = data.delivery.vehicle_number || '';
                    document.getElementById('settleDaDriver').value = data.delivery.driver_name || '';
                    document.getElementById('settleDaPartner').value = data.delivery.partner_name || '';
                }

                renderSettleDoubleEntries();

                if (data.delivery && data.delivery.accounting_entries_json) {
                    try {
                        const accEntries = JSON.parse(data.delivery.accounting_entries_json);
                        document.querySelectorAll('.settle-de-select, .settle-payment-debit, .settle-payment-credit').forEach(sel => {
                            const id = sel.getAttribute('data-id');
                            const type = sel.getAttribute('data-type');
                            let val = null;
                            if (accEntries[type]) {
                                if (accEntries[type][id]) {
                                    val = accEntries[type][id];
                                } else {
                                    const rawId = id.replace(/^(pay_|inv_)/, '');
                                    val = accEntries[type][rawId];
                                }
                            }
                            if (val) {
                                sel.value = val;
                            }
                        });
                    } catch(e) {}
                }

                if (isReadOnly) {
                    document.querySelectorAll('.settle-de-select, .settle-payment-debit, .settle-payment-credit, .settle-payment-chk, .settle-invoice-chk, .settle-payment-adj, .settle-payment-notes').forEach(el => {
                        el.disabled = true;
                    });
                }

                checkSettleVerification();
            });
    }

    function applyFinalizeDefensiveGuard(routeId, delId) {
        const guardEl = document.getElementById('tab11GuardContainer');
        const contentEl = document.getElementById('tab11ContentContainer');
        if (!guardEl || !contentEl) return false;

        let isBlocked = false;
        let title = '';
        let desc = '';

        if (currentRouteStatus === 'Active') {
            isBlocked = true;
            title = 'Route Still Active';
            desc = 'Route summary and finalization options are not available until the representative has ended the route from their app.';
        }

        if (isBlocked) {
            guardEl.innerHTML = `
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 45px 20px; text-align: center; max-width: 580px; margin: 40px auto; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="width: 60px; height: 60px; background: #fffbeb; color: #d97706; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; font-size: 28px;">
                        <i class="ph ph-warning-circle"></i>
                    </div>
                    <h4 style="margin: 0 0 10px 0; font-size: 15px; font-weight: bold; color: #0f172a;">${title}</h4>
                    <p style="margin: 0; font-size: 12px; color: #64748b; line-height: 1.6;">${desc}</p>
                </div>
            `;
            guardEl.style.display = 'block';
            contentEl.style.display = 'none';
            return true;
        } else {
            guardEl.style.display = 'none';
            contentEl.style.display = 'block';
            return false;
        }
    }

    function loadTab11Finalize(routeId) {
        const rdata = document.getElementById('route_data_' + routeId);
        const delId = rdata ? rdata.getAttribute('data-delivery-id') : null;

        if (applyFinalizeDefensiveGuard(routeId, delId)) {
            return;
        }

        fetchSecure('<?= APP_URL ?>/RepTracking/api_get_delivery_details/' + (delId || 0) + '?route_id=' + routeId)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success') return;
                currentDeliveryDetails = data;
                
                const delivery = data.delivery || {};
                const balancing = data.balancing || {};
                
                document.getElementById('sumRouteName').innerText = delivery.route_name || '--';
                document.getElementById('sumRepName').innerText = ((delivery.first_name || '') + ' ' + (delivery.last_name || '')).trim() || '--';
                document.getElementById('sumVehicleNumber').innerText = delivery.vehicle_number || '--';
                document.getElementById('sumDriverName').innerText = delivery.driver_name || '--';
                document.getElementById('sumPartnerName').innerText = delivery.partner_name || 'None';
                
                const startMeter = parseFloat(delivery.start_meter) || 0;
                const endMeter = parseFloat(delivery.end_meter) || 0;
                document.getElementById('sumStartMeter').innerText = startMeter.toLocaleString() + ' KM';
                document.getElementById('sumEndMeter').innerText = endMeter.toLocaleString() + ' KM';
                document.getElementById('sumDistanceTraveled').innerText = Math.max(0, endMeter - startMeter).toLocaleString() + ' KM';
                
                const cashSales = parseFloat(balancing.cash_sales) || 0;
                const chequeSales = parseFloat(balancing.cheque_sales) || 0;
                const bankSales = parseFloat(balancing.bank_sales) || 0;
                const creditSales = parseFloat(balancing.credit_sales) || 0;
                const totalSales = cashSales + chequeSales + bankSales + creditSales;
                
                document.getElementById('sumCashSales').innerText = 'Rs ' + cashSales.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('sumChequeSales').innerText = 'Rs ' + chequeSales.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('sumBankSales').innerText = 'Rs ' + bankSales.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('sumCreditSales').innerText = 'Rs ' + creditSales.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('sumTotalSales').innerText = 'Rs ' + totalSales.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                
                let driverCashCollections = 0.0;
                if (balancing.payments) {
                    balancing.payments.forEach(p => {
                        if (p.payment_method === 'Cash' && !p.mobile_rep_id) {
                            driverCashCollections += parseFloat(p.amount) || 0;
                        }
                    });
                }
                
                let cashDenoms = {};
                try {
                    if (delivery.cash_denominations) {
                        cashDenoms = JSON.parse(delivery.cash_denominations);
                    }
                } catch(e) {}
                
                let actualCash = 0.0;
                const denomList = [5000, 2000, 1000, 500, 100, 50, 20];
                denomList.forEach(den => {
                    const cnt = parseInt(cashDenoms[den]) || 0;
                    actualCash += cnt * den;
                });
                actualCash += parseFloat(cashDenoms['coins']) || 0;
                
                const variance = actualCash - driverCashCollections;
                
                document.getElementById('sumExpectedCash').innerText = 'Rs ' + driverCashCollections.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('sumActualCash').innerText = 'Rs ' + actualCash.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                
                const varEl = document.getElementById('sumCashVariance');
                varEl.innerText = (variance >= 0 ? '+' : '') + 'Rs ' + variance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                if (Math.abs(variance) < 0.01) {
                    varEl.style.color = '#166534';
                } else if (variance < 0) {
                    varEl.style.color = '#dc2626';
                } else {
                    varEl.style.color = '#ef6c00';
                }
                
                checkSettleVerification();
            });
    }

    function saveAccountingDraft() {
        if (!currentRouteId || !currentDeliveryDetails) return;

        const debitAccounts = {};
        const creditAccounts = {};
        document.querySelectorAll('.settle-de-select').forEach(sel => {
            const id = sel.getAttribute('data-id');
            const type = sel.getAttribute('data-type');
            const val = parseInt(sel.value);
            if (type === 'debit') { debitAccounts[id] = val; } else { creditAccounts[id] = val; }
        });

        // Also add the collection debit/credit accounts so they are preserved in delivery accounting json as draft
        document.querySelectorAll('.settle-payment-row').forEach(row => {
            const payId = parseInt(row.getAttribute('data-pay-id'));
            const debAcc = parseInt(row.querySelector('.settle-payment-debit').value);
            const credAcc = parseInt(row.querySelector('.settle-payment-credit').value);
            debitAccounts['pay_' + payId] = debAcc;
            creditAccounts['pay_' + payId] = credAcc;
        });

        const accountingData = {
            debit: debitAccounts,
            credit: creditAccounts
        };

        const deliveryId = currentDeliveryDetails.delivery.id;

        const updates = [];
        document.querySelectorAll('.settle-payment-row').forEach(row => {
            const payId = parseInt(row.getAttribute('data-pay-id'));
            const isVerified = row.querySelector('.settle-payment-chk').checked ? 1 : 0;
            const debAcc = parseInt(row.querySelector('.settle-payment-debit').value);
            const credAcc = parseInt(row.querySelector('.settle-payment-credit').value);
            const adjAmt = parseFloat(row.querySelector('.settle-payment-adj').value);
            const notes = row.querySelector('.settle-payment-notes').value;

            updates.push({
                id: payId,
                is_verified: isVerified,
                is_flagged: 0,
                adjusted_amount: adjAmt,
                verification_notes: notes,
                debit_account_id: debAcc,
                credit_account_id: credAcc
            });
        });

        // 1. Save collections verification
        fetchSecure('<?= APP_URL ?>/RepTracking/api_verify_collections', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ updates: updates })
        })
        .then(res => res.json())
        .then(colData => {
            if (colData.status !== 'success') {
                alert("Error saving collections: " + colData.message);
                return;
            }

            // 2. Save accounting entries json
            fetchSecure('<?= APP_URL ?>/RepTracking/api_save_accounting_entries', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    delivery_id: deliveryId, 
                    route_id: currentRouteId,
                    accounting_entries_json: accountingData 
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert("Accounting mappings and collection verification draft saved successfully!");
                    if (data.delivery_id) {
                        const rdata = document.getElementById('route_data_' + currentRouteId);
                        if (rdata) {
                            rdata.setAttribute('data-delivery-id', data.delivery_id);
                        }
                        if (currentDeliveryDetails && currentDeliveryDetails.delivery) {
                            currentDeliveryDetails.delivery.id = data.delivery_id;
                        }
                    }
                    onRouteDataChanged();
                } else {
                    alert("Error: " + data.message);
                }
            });
        });
    }

    function checkSettleVerification() {
        console.log("[SettleVerify] checkSettleVerification started.");
        const verifyStockCheck = document.getElementById('settleVerifyStock');
        let verifyStock = verifyStockCheck ? verifyStockCheck.checked : false;

        // Fallback: check if the global delivery details already have return stock saved
        if (!verifyStock && currentDeliveryDetails && currentDeliveryDetails.delivery && currentDeliveryDetails.delivery.return_stock_json !== null && currentDeliveryDetails.delivery.return_stock_json !== '') {
            console.log("[SettleVerify] Fallback: Found saved return stock in database. Auto-checking checkbox.");
            verifyStock = true;
            if (verifyStockCheck) {
                verifyStockCheck.checked = true;
            }
        }
        console.log("[SettleVerify] verifyStock state:", verifyStock);

        const btn = document.getElementById('settleSubmitBtn');
        const text = document.getElementById('settleStatusText');
        if (!btn || !text) return;

        if (currentRouteStatus === 'Completed' || currentRouteStatus === 'Finalized') {
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.style.cursor = 'not-allowed';
            text.innerHTML = '<span style="color:#2e7d32; font-weight:bold;">Route Finalized & Settled</span>';
            return;
        }

        let allCollectionsApproved = true;
        let pendingOrFlaggedCount = 0;
        
        // Check collections verification from Cash/Cheques Posting DOM checkboxes
        document.querySelectorAll('.settle-payment-chk').forEach(chk => {
            if (!chk.checked) {
                allCollectionsApproved = false;
                pendingOrFlaggedCount++;
            }
        });

        if (allCollectionsApproved && verifyStock) {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
            text.innerHTML = '<span style="color:#2e7d32; font-weight:bold;">Verification Complete!</span> Ready to settle balancing.';
        } else {
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.style.cursor = 'not-allowed';
            
            let msg = '';
            if (!allCollectionsApproved) {
                msg += `Please approve/verify all collections under Cash/Cheques Posting tab (${pendingOrFlaggedCount} remaining). `;
            }
            if (!verifyStock) {
                msg += 'Please verify Return Stock checkbox under Return Stock tab.';
            }
            text.innerHTML = `<span style="color:#dc2626; font-weight:bold;">Locked:</span> ${msg}`;
        }
    }

    let settleActiveDeTab = 'collections';
    function switchSettleDeTab(tab) {
        settleActiveDeTab = tab;
        document.getElementById('settleDeTabCollectionsBtn').classList.toggle('active', tab === 'collections');
        document.getElementById('settleDeTabSalesBtn').classList.toggle('active', tab === 'sales');
        document.getElementById('settleDeCollectionsContainer').style.display = tab === 'collections' ? 'block' : 'none';
        document.getElementById('settleDeSalesContainer').style.display = tab === 'sales' ? 'block' : 'none';
    }

    function renderSettleDeAccountSelect(id, type, selectedCode) {
        let optionsHtml = '';
        globalAllAccounts.forEach(acc => {
            let isSel = acc.account_code === selectedCode ? 'selected' : '';
            optionsHtml += `<option value="${acc.id}" ${isSel}>${acc.account_code} - ${acc.account_name}</option>`;
        });
        return `<select class="settle-de-select" data-id="${id}" data-type="${type}" style="padding:4px 8px; font-size:12px; border-radius:4px; border:1px solid #ccc; width:100%;">${optionsHtml}</select>`;
    }

    function renderSettleDeAccountSelectForPayment(id, type, selectedId, isReadOnly) {
        let optionsHtml = '';
        globalAllAccounts.forEach(acc => {
            let isSel = String(acc.id) === String(selectedId) ? 'selected' : '';
            optionsHtml += `<option value="${acc.id}" ${isSel}>${acc.account_code} - ${acc.account_name}</option>`;
        });
        return `<select class="settle-payment-${type}" data-id="${id}" data-type="${type}" style="padding:4px 8px; font-size:11px; border-radius:4px; border:1px solid #cbd5e1; width:100%;" ${isReadOnly ? 'disabled' : ''}>${optionsHtml}</select>`;
    }

    function renderSettleDoubleEntries() {
        const colContainer = document.getElementById('settleDeCollectionsContainer');
        const salesContainer = document.getElementById('settleDeSalesContainer');
        
        colContainer.innerHTML = '';
        salesContainer.innerHTML = '';

        if (!currentDeliveryDetails) return;

        const payments = currentDeliveryDetails.balancing.payments || [];
        const invoices = currentDeliveryDetails.invoices || [];

        // 1. Collections
        if (payments.length === 0) {
            colContainer.innerHTML = '<p style="color:#888; text-align:center;">No payments logged on this trip.</p>';
        } else {
            let tableHTML = `
                <div style="border: 0.5px solid var(--c-separator); border-radius: var(--r-md); background: var(--c-surface); overflow: hidden;">
                    <table class="data-table" style="margin-top:0;">
                        <thead>
                            <tr style="background:var(--c-surface2);">
                                <th style="text-align:left; width:22%;">Customer / Pay</th>
                                <th style="text-align:right; width:12%;">Collected</th>
                                <th style="text-align:center; width:8%;">Approve</th>
                                <th style="text-align:left; width:20%;">Debit Account</th>
                                <th style="text-align:left; width:20%;">Credit Account</th>
                                <th style="text-align:right; width:10%;">Adjusted</th>
                                <th style="text-align:left; width:10%;">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            payments.forEach(p => {
                const isVerified = parseInt(p.is_verified) === 1;
                const isReadOnly = (currentRouteStatus === 'Completed' || currentRouteStatus === 'Finalized');
                const amt = parseFloat(p.amount);
                const adjustedVal = p.adjusted_amount !== null ? p.adjusted_amount : p.amount;

                let defaultDebitCode = '1000'; // Cash
                if (p.payment_method === 'Cheque') { defaultDebitCode = '1010'; }
                else if (p.payment_method === 'Bank Transfer') { defaultDebitCode = '1605'; }

                const selectedDebit = p.debit_account_id || getAccountIdByCode(defaultDebitCode);
                const selectedCredit = p.credit_account_id || getAccountIdByCode('1200');

                tableHTML += `
                    <tr class="settle-payment-row" data-pay-id="${p.id}" style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:6px 4px;">
                            <strong>${p.customer_name}</strong><br>
                            <span style="font-size:10px; color:#64748b;">${p.payment_method} ${p.cheque_number ? '(' + p.cheque_number + ')' : (p.reference ? '(' + p.reference + ')' : '')}</span>
                            ${isReadOnly ? '<br><span style="font-size:10px; color:#2e7d32; font-weight:bold;">Posted to GL</span>' : ''}
                        </td>
                        <td style="padding:6px 4px; text-align:right; font-family:monospace; font-weight:bold;">
                            Rs ${amt.toFixed(2)}
                        </td>
                        <td style="padding:6px 4px; text-align:center;">
                            <input type="checkbox" class="settle-payment-chk" value="${p.id}" 
                                   ${(isVerified || isReadOnly) ? 'checked' : ''} ${isReadOnly ? 'disabled' : ''} 
                                   onchange="checkSettleVerification(); updateSidebarProgress();"
                                   style="width:16px; height:16px; cursor:${isReadOnly ? 'not-allowed' : 'pointer'};" />
                        </td>
                        <td style="padding:6px 4px;">
                            ${renderSettleDeAccountSelectForPayment('pay_' + p.id, 'debit', selectedDebit, isReadOnly)}
                        </td>
                        <td style="padding:6px 4px;">
                            ${renderSettleDeAccountSelectForPayment('pay_' + p.id, 'credit', selectedCredit, isReadOnly)}
                        </td>
                        <td style="padding:6px 4px; text-align:center;">
                            <input type="number" step="0.01" min="0" class="settle-payment-adj" value="${parseFloat(adjustedVal).toFixed(2)}"
                                   ${isReadOnly ? 'disabled' : ''}
                                   style="width:80px; padding:3px; border:1px solid #cbd5e1; border-radius:4px; text-align:right; font-family:monospace; font-size:11px;" />
                        </td>
                        <td style="padding:6px 4px; text-align:center;">
                            <input type="text" class="settle-payment-notes" value="${p.verification_notes || ''}" placeholder="Notes"
                                   ${isReadOnly ? 'disabled' : ''}
                                   style="width:100px; padding:3px; border:1px solid #cbd5e1; border-radius:4px; font-size:11px;" />
                        </td>
                    </tr>
                `;
            });

            tableHTML += `
                        </tbody>
                    </table>
                </div>
            `;
            colContainer.innerHTML = tableHTML;
        }

        // 2. Sales
        const deliveredInvoices = invoices.filter(inv => inv.delivery_status === 'Delivered');
        if (deliveredInvoices.length === 0) {
            salesContainer.innerHTML = '<p style="color:#888; text-align:center;">No delivered sales invoices on this trip.</p>';
        } else {
            deliveredInvoices.forEach(inv => {
                salesContainer.innerHTML += `
                    <div style="display:flex; justify-content:space-between; align-items:center; background:#fafafa; border:1px solid #eee; padding:10px; margin-bottom:8px; border-radius:6px; flex-wrap:wrap; gap:10px;">
                        <div style="flex:1; min-width:200px;">
                            <label style="display:flex; align-items:center; gap:8px; font-weight:bold;">
                                <input type="checkbox" class="settle-invoice-chk" value="${inv.id}" checked>
                                ${inv.invoice_number} (${inv.customer_name})
                            </label>
                        </div>
                        <div style="font-weight:bold; color:#0066cc;">Rs ${parseFloat(inv.true_grand_total).toFixed(2)}</div>
                        <div style="display:flex; gap:10px; flex:2;">
                            <div style="flex:1;">
                                <span style="font-size:9px; color:#666;">Debit Account (AR)</span>
                                ${renderSettleDeAccountSelect('inv_' + inv.id, 'debit', '1090')}
                            </div>
                            <div style="flex:1;">
                                <span style="font-size:9px; color:#666;">Credit Account (Sales)</span>
                                ${renderSettleDeAccountSelect('inv_' + inv.id, 'credit', '3000')}
                            </div>
                        </div>
                    </div>
                `;
            });
        }
    }

    function submitFinalSettle() {
        const vehicle = document.getElementById('settleDaVehicle').value;
        const driver = document.getElementById('settleDaDriver').value;
        const partner = document.getElementById('settleDaPartner').value;

        if (!vehicle) { alert("Please select a Vehicle Number."); return; }
        if (!driver) { alert("Please select a Driver Name."); return; }

        if (!confirm("Are you sure you want to FINALIZE and SETTLE this delivery route?\n\nThis will post all selected collections to GL and update inventory.")) {
            return;
        }

        const d = document.getElementById('route_data_' + currentRouteId);
        const delId = d.getAttribute('data-delivery-id');

        const selectedPaymentIds = [];
        document.querySelectorAll('.settle-payment-chk:checked').forEach(cb => {
            selectedPaymentIds.push(parseInt(cb.value));
        });

        const selectedInvoiceIds = [];
        document.querySelectorAll('.settle-invoice-chk:checked').forEach(cb => {
            selectedInvoiceIds.push(parseInt(cb.value));
        });

        const debitAccounts = {};
        const creditAccounts = {};
        document.querySelectorAll('.settle-de-select').forEach(sel => {
            const id = sel.getAttribute('data-id');
            const type = sel.getAttribute('data-type');
            const val = parseInt(sel.value);
            if (type === 'debit') { debitAccounts[id] = val; } else { creditAccounts[id] = val; }
        });
        document.querySelectorAll('.settle-payment-debit').forEach(sel => {
            const id = sel.getAttribute('data-id');
            const val = parseInt(sel.value);
            debitAccounts[id] = val;
        });
        document.querySelectorAll('.settle-payment-credit').forEach(sel => {
            const id = sel.getAttribute('data-id');
            const val = parseInt(sel.value);
            creditAccounts[id] = val;
        });

        const returnedItems = [];
        document.querySelectorAll('.actual-returned-input').forEach(input => {
            returnedItems.push({
                item_name: input.getAttribute('data-name'),
                item_id: parseInt(input.getAttribute('data-item-id') || 0),
                variation_option_id: parseInt(input.getAttribute('data-var-id') || 0),
                loaded_qty: parseInt(input.getAttribute('data-loaded') || 0),
                delivered_qty: parseInt(input.getAttribute('data-delivered') || 0),
                actual_returned_qty: parseInt(input.value || 0)
            });
        });

        const btn = document.getElementById('settleSubmitBtn');
        btn.disabled = true;
        btn.innerText = 'Settling Route... ';

        fetchSecure('<?= APP_URL ?>/RepTracking/finalize', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                delivery_id: parseInt(delId),
                selected_payment_ids: selectedPaymentIds,
                selected_invoice_ids: selectedInvoiceIds,
                debit_accounts: debitAccounts,
                credit_accounts: creditAccounts,
                returned_items: returnedItems,
                vehicle_number: vehicle,
                driver_name: driver,
                partner_name: partner
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("Settle balancing successfully completed! Route marked as Completed.");
                window.shouldReloadOnClose = true;
                openBalancingReportModal(delId);
            } else {
                alert("Error: " + data.message);
                btn.disabled = false;
                btn.innerText = '<i class="ph ph-scales"></i> Settle Balancing & Finalize Route';
                checkSettleVerification();
            }
        });
    }

    window.shouldReloadOnClose = false;

    function openBalancingReportModal(delId) {
        const modal = document.getElementById('balancingReportModal');
        const iframe = document.getElementById('balancingReportIframe');
        if (modal && iframe) {
            iframe.src = '<?= APP_URL ?>/RepTracking/balancing_report/' + delId;
            modal.style.display = 'flex';
        }
    }

    function closeBalancingReportModal() {
        const modal = document.getElementById('balancingReportModal');
        const iframe = document.getElementById('balancingReportIframe');
        if (modal && iframe) {
            modal.style.display = 'none';
            iframe.src = 'about:blank';
        }
        if (window.shouldReloadOnClose) {
            window.shouldReloadOnClose = false;
            window.location.reload();
        }
    }

    function printBalancingReportFromModal() {
        const iframe = document.getElementById('balancingReportIframe');
        if (iframe && iframe.contentWindow) {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }
    }

    function printBalancingReport() {
        const d = document.getElementById('route_data_' + currentRouteId);
        const delId = d ? d.getAttribute('data-delivery-id') : null;
        if (delId) { openBalancingReportModal(delId); }
    }

    function printLoadingSheetSpreadsheet() {
        const d = document.getElementById('route_data_' + currentRouteId);
        const delId = d ? d.getAttribute('data-delivery-id') : null;
        if (delId) { window.open('<?= APP_URL ?>/RepTracking/spreadsheet/' + delId, '_blank'); }
    }

    function exportCSV() {
        const d = document.getElementById('route_data_' + currentRouteId);
        const delId = d ? d.getAttribute('data-delivery-id') : null;
        if (delId) { window.location.href = '<?= APP_URL ?>/RepTracking/export_csv/' + delId; }
    }

    function getDataTypeFromStatus(status) {
        if (status === 'Active') return 'active';
        if (status === 'Pending GL') return 'pending_gl';
        if (status === 'Adjustments') return 'adjustments';
        if (status === 'Loading') return 'loading';
        if (status === 'Variance Adjustment') return 'variance';
        if (status === 'Finalizing' || status === 'Delivery Arranged') return 'finalizing';
        return 'completed';
    }

    function advanceRouteStatus(targetStatus) {
        if (!confirm(`Are you sure you want to advance this route to "${targetStatus}" stage?`)) {
            return;
        }
        fetchSecure('<?= APP_URL ?>/RepTracking/api_update_route_status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ route_id: currentRouteId, status: targetStatus })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(`Route advanced to ${targetStatus}`);
                const filterType = getDataTypeFromStatus(targetStatus);
                window.location.href = window.location.pathname + `?route_id=${currentRouteId}&filter=${filterType}`;
            } else {
                if (data.show_override) {
                    if (confirm(data.message + "\n\nDo you want to override this block and force advance the status?")) {
                        fetchSecure('<?= APP_URL ?>/RepTracking/api_update_route_status', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ route_id: currentRouteId, status: targetStatus, admin_override: true })
                        })
                        .then(res2 => res2.json())
                        .then(data2 => {
                            if (data2.status === 'success') {
                                alert(`Route advanced to ${targetStatus} (via Override)`);
                                const filterType = getDataTypeFromStatus(targetStatus);
                                window.location.href = window.location.pathname + `?route_id=${currentRouteId}&filter=${filterType}`;
                            } else {
                                alert("Error: " + data2.message);
                            }
                        });
                    }
                } else {
                    alert("Error: " + data.message);
                }
            }
        });
    }

    function redirectToAddInvoice() {
        if (currentRouteId) {
            window.location.href = '<?= APP_URL ?>/sales/create?type=sales_order&route_id=' + currentRouteId + '&back_url=' + encodeURIComponent(window.location.href);
        }
    }

    function printRouteInvoices() {
        if (currentRouteId) {
            window.open('<?= APP_URL ?>/RepTracking/print_route_invoices/' + currentRouteId, '_blank');
        }
    }

    function printRouteInvoicesSummary() {
        if (currentRouteId) {
            window.open('<?= APP_URL ?>/RepTracking/print_route_invoices_summary/' + currentRouteId, '_blank');
        }
    }

    function openInvoiceSlider(invoiceId) {
        const backdrop = document.getElementById('invoiceSliderBackdrop');
        const iframe = document.getElementById('invoiceIframe');
        iframe.src = 'about:blank';
        setTimeout(() => {
            iframe.src = '<?= APP_URL ?>/sales/show/' + invoiceId + '?hide_buttons=1';
        }, 50);
        backdrop.style.display = 'flex';
    }

    function closeInvoiceSlider() {
        document.getElementById('invoiceSliderBackdrop').style.display = 'none';
        document.getElementById('invoiceIframe').src = 'about:blank';
    }
    function deleteSalesOrder() {
        const invoiceId = document.getElementById('btnDeleteInvoice').getAttribute('data-invoice-id');
        if (!invoiceId) return;
        if (!confirm("Are you sure you want to delete this Sales Order? This will release reserved stock back to inventory and cannot be undone.")) {
            return;
        }
        fetchSecure('<?= APP_URL ?>/RepTracking/api_delete_sales_order/' + invoiceId, {
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("Success: " + data.message);
                closeInvoiceSlider();
                loadRouteDetails(currentRouteId);
            } else {
                alert("Error: " + data.message);
            }
        });
    }
    // --- GPS Path Map Handlers ---
    function openMapModal() {
        document.getElementById('mapModalBackdrop').style.display = 'flex';
        loadRoutePath(currentRouteId);
    }

    function closeMapModal() {
        document.getElementById('mapModalBackdrop').style.display = 'none';
    }

    function initRoutePathMap() {
        if (routeMap !== null) return;
        routeMap = L.map('routePathMap').setView([7.8731, 80.7718], 8);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(routeMap);
    }

    function clearRoutePathMap() {
        routeMapLayers.forEach(layer => routeMap.removeLayer(layer));
        routeMapLayers = [];
    }

    function loadRoutePath(routeId) {
        document.getElementById('mapEmptyOverlay').style.display = 'flex';
        document.getElementById('mapEmptyOverlay').innerText = 'Loading route path...';
        document.getElementById('pathStepList').style.display = 'none';

        initRoutePathMap();
        clearRoutePathMap();

        fetch('<?= APP_URL ?>/RepTracking/api_get_route_path/' + routeId)
            .then(r => r.json())
            .then(data => {
                if (data.status !== 'success' || !data.path) {
                    document.getElementById('mapEmptyOverlay').innerText = 'Could not load route path.';
                    return;
                }
                renderRoutePath(data.path);
            })
            .catch(() => {
                document.getElementById('mapEmptyOverlay').innerText = 'Failed to load route path.';
            });
    }

    function renderRoutePath(path) {
        const wps = path.waypoints || [];
        document.getElementById('pathPointCount').innerText = wps.length ? `(${wps.length} points)` : '(no GPS)';
        document.getElementById('modalRouteName').innerText = path.route_name || '';

        const stepOl = document.getElementById('pathStepOl');
        stepOl.innerHTML = '';

        if (wps.length === 0) {
            document.getElementById('mapEmptyOverlay').style.display = 'flex';
            document.getElementById('mapEmptyOverlay').innerHTML = 'No GPS points recorded for this route.';
            document.getElementById('pathStepList').style.display = 'none';
            setTimeout(() => routeMap.invalidateSize(), 100);
            return;
        }

        document.getElementById('mapEmptyOverlay').style.display = 'none';
        document.getElementById('pathStepList').style.display = 'block';

        const latlngs = [];
        wps.forEach((wp) => {
            const latlng = [wp.lat, wp.lng];
            latlngs.push(latlng);

            let icon = pathBlueIcon;
            let stepClass = 'path-step-invoice';
            if (wp.type === 'start') { icon = pathGreenIcon; stepClass = 'path-step-start'; }
            else if (wp.type === 'end') { icon = pathRedIcon; stepClass = 'path-step-end'; }

            const marker = L.marker(latlng, { icon: icon }).addTo(routeMap);
            marker.bindPopup(`<strong>${wp.name}</strong><br>${wp.description}<br><span style="font-size:10px; color:#666;">${wp.time}</span>`);
            routeMapLayers.push(marker);

            stepOl.innerHTML += `<li class="${stepClass}"><strong>${wp.time}</strong> - ${wp.name} (${wp.description})</li>`;
        });

        if (latlngs.length > 1) {
            const polyline = L.polyline(latlngs, { color: '#0066cc', weight: 4, opacity: 0.7 }).addTo(routeMap);
            routeMapLayers.push(polyline);
            routeMap.fitBounds(polyline.getBounds(), { padding: [30, 30] });
        } else {
            routeMap.setView(latlngs[0], 14);
        }

        setTimeout(() => routeMap.invalidateSize(), 100);
    }

    // --- Route Binding Handlers ---
    function getEligibleBindingRoutes() {
        const routes = [];
        document.querySelectorAll('.route-item').forEach(item => {
            const rType = item.getAttribute('data-route-type');
            if (rType && rType !== 'completed') {
                const id = item.id.replace('route_', '');
                const dataDiv = document.getElementById('route_data_' + id);
                if (dataDiv) {
                    routes.push({ id: parseInt(id), name: dataDiv.getAttribute('data-rname'), rep: dataDiv.getAttribute('data-rep') });
                }
            }
        });
        return routes;
    }

    function openRouteBindingModal() {
        document.getElementById('rbBoundName').value = '';
        document.getElementById('rbSlotsContainer').innerHTML = '';
        rbSlotsCount = 0;
        addBindingSlot();
        addBindingSlot();
        document.getElementById('routeBindingModal').style.display = 'flex';
    }

    function closeRouteBindingModal() {
        document.getElementById('routeBindingModal').style.display = 'none';
    }

    function addBindingSlot() {
        rbSlotsCount++;
        const index = rbSlotsCount;
        const eligibleRoutes = getEligibleBindingRoutes();
        
        let optionsHtml = '<option value="">-- Choose Route --</option>';
        eligibleRoutes.forEach(r => {
            optionsHtml += `<option value="${r.id}">${r.name} (Rep: ${r.rep})</option>`;
        });
        
        const slotHtml = `
            <div class="rb-slot-column" id="rb_slot_col_${index}" style="position: relative;">
                ${index > 2 ? `<button type="button" onclick="removeBindingSlot(${index})" style="position: absolute; top: 10px; right: 10px; border: none; background: #dc2626; color: #fff; width: 22px; height: 22px; border-radius: 50%; cursor: pointer; font-size: 11px; display: flex; align-items: center; justify-content: center; font-weight: bold; padding:0;">✕</button>` : ''}
                <h5 style="margin: 0 0 5px 0; color: #3f51b5; font-size: 12px; font-weight: bold; text-transform: uppercase;">Slot ${index}</h5>
                <div class="rb-slot-box">
                    <div style="font-size: 20px; color: #cbd5e1; margin-bottom: 6px;" id="rb_slot_icon_${index}">+</div>
                    <select class="rb-slot-select" id="rb_select_${index}" onchange="onBindingSlotRouteSelect(${index}, this)">
                        ${optionsHtml}
                    </select>
                </div>
                <div class="rb-bill-list" id="rb_bills_${index}"></div>
            </div>
        `;
        document.getElementById('rbSlotsContainer').insertAdjacentHTML('beforeend', slotHtml);
    }

    function removeBindingSlot(index) {
        const el = document.getElementById(`rb_slot_col_${index}`);
        if (el) el.remove();
    }

    function onBindingSlotRouteSelect(index, select) {
        const routeId = select.value;
        const billsContainer = document.getElementById(`rb_bills_${index}`);
        const icon = document.getElementById(`rb_slot_icon_${index}`);
        
        if (!routeId) {
            billsContainer.style.display = 'none';
            billsContainer.innerHTML = '';
            icon.innerText = '+';
            return;
        }
        
        // Find the selected route object
        const eligibleRoutes = getEligibleBindingRoutes();
        const selectedRoute = eligibleRoutes.find(r => r.id === parseInt(routeId));
        const selectedRouteName = selectedRoute ? selectedRoute.name : '';
        const selectedRepName = selectedRoute ? selectedRoute.rep : '';

        icon.innerText = '<i class="ph ph-link"></i>';
        billsContainer.style.display = 'block';
        billsContainer.innerHTML = '<p style="text-align: center; color: #888;">Loading details... </p>';
        
        fetch('<?= APP_URL ?>/RepTracking/api_get_route_details/' + routeId)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success' || !data.bills) {
                    billsContainer.innerHTML = '<p style="text-align: center; color: #888;">Error loading details.</p>';
                    return;
                }
                
                // Calculate values for Route Preview
                const invoices = data.bills;
                const invoiceCount = invoices.length;
                const uniqueCustomers = new Set(invoices.map(b => b.customer_id)).size;
                const totalRouteValue = invoices.reduce((sum, b) => sum + parseFloat(b.true_grand_total), 0);

                let previewHtml = `
                    <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; margin-bottom: 12px; font-size: 11px; line-height: 1.5; color: #334155; text-align: left; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        <div style="margin-bottom: 3px;"><strong>Route:</strong> ${selectedRouteName}</div>
                        <div style="margin-bottom: 3px;"><strong>Route ID:</strong> #${routeId}</div>
                        <div style="margin-bottom: 3px;"><strong>Rep Name:</strong> ${selectedRepName}</div>
                        <div style="margin-bottom: 3px;"><strong>Customers Count:</strong> ${uniqueCustomers}</div>
                        <div style="margin-bottom: 3px;"><strong>Invoice Count:</strong> ${invoiceCount}</div>
                        <div><strong>Total Value:</strong> <strong style="color: #16a34a;">Rs ${totalRouteValue.toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong></div>
                    </div>
                `;

                if (invoices.length === 0) {
                    previewHtml += '<p style="text-align: center; color: #888; font-size: 11px;">No sales orders in this route.</p>';
                    billsContainer.innerHTML = previewHtml;
                    return;
                }
                
                previewHtml += '<div style="font-weight: bold; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; margin-bottom: 8px; font-size: 10px; text-transform: uppercase; color: #475569;">Sales Orders / Bills</div>';
                previewHtml += '<div style="max-height: 150px; overflow-y: auto; display: flex; flex-direction: column; gap: 4px;">';
                invoices.forEach(b => {
                    let trueTotal = parseFloat(b.true_grand_total).toLocaleString('en-IN', {minimumFractionDigits: 2});
                    previewHtml += `
                        <div class="rb-bill-item" style="display: flex; justify-content: space-between; align-items: flex-start; background: #fff; border: 1px solid #e2e8f0; padding: 6px; border-radius: 4px; font-size: 11px;">
                            <div style="display: flex; flex-direction: column; max-width: 65%;">
                                <strong>${b.invoice_number}</strong>
                                <span style="color: #64748b; font-size: 10px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${b.customer_name}</span>
                            </div>
                            <strong style="font-family: monospace; color: #0f172a; white-space: nowrap;">Rs ${trueTotal}</strong>
                        </div>
                    `;
                });
                previewHtml += '</div>';
                billsContainer.innerHTML = previewHtml;
            });
    }

    function submitRouteBinding() {
        const boundName = document.getElementById('rbBoundName').value.trim();
        if (!boundName) { alert("Please enter a custom name for the bound route."); return; }
        
        const routeIds = [];
        document.querySelectorAll('.rb-slot-select').forEach(select => {
            if (select.value) { routeIds.push(parseInt(select.value)); }
        });
        
        const uniqueRouteIds = [...new Set(routeIds)];
        if (uniqueRouteIds.length < 2) { alert("Please select at least 2 distinct routes to bind."); return; }
        if (uniqueRouteIds.length !== routeIds.length) { alert("Please make sure you do not select the same route in multiple slots."); return; }
        
        if (!confirm(`Are you sure you want to bind these ${uniqueRouteIds.length} routes together under "${boundName}"?`)) { return; }
        
        fetch('<?= APP_URL ?>/RepTracking/api_create_binding', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ binding_name: boundName, route_ids: uniqueRouteIds })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("Success: " + data.message);
                closeRouteBindingModal();
                // Redirect/reload switching filter to Adjustments
                window.location.href = window.location.pathname + `?filter=adjustments`;
            } else {
                alert("Error: " + data.message);
            }
        });
    }

    function unbindActiveRoute() {
        const btnUnbind = document.getElementById('btnUnbindRoute');
        const bindingId = btnUnbind ? btnUnbind.getAttribute('data-binding-id') : null;
        if (!bindingId) { alert("No active route binding identified."); return; }

        if (!confirm("Are you sure you want to undo this route binding? The routes will be separated back to their original states and listed individually.")) { return; }

        fetch('<?= APP_URL ?>/RepTracking/api_unbind_route', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ binding_id: parseInt(bindingId) })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("Success: " + data.message);
                window.location.href = window.location.pathname + `?filter=adjustments`;
            } else {
                alert("Error: " + data.message);
            }
        });
    }

    function unbindCombinedRoute() {
        if (!currentRouteId) return;
        if (!confirm("Are you sure you want to Undo this route binding? All invoices, loading data, and collections will be restored to their original separate routes, and this combined route will be removed.")) {
            return;
        }
        
        fetch('<?= APP_URL ?>/RepTracking/api_unbind_route', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ route_id: currentRouteId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                window.location.href = window.location.pathname + `?filter=adjustments`;
            } else {
                alert("Error: " + data.message);
            }
        });
    }

    // --- Attach Invoice Modal Handlers ---
    function openAttachInvoiceModal() {
        document.getElementById('invoiceSearchInput').value = '';
        document.getElementById('soFilterStartDate').value = '';
        document.getElementById('soFilterEndDate').value = '';
        document.getElementById('soFilterStatus').value = '';
        document.getElementById('unattachedInvoicesContainer').innerHTML = '<p style="text-align: center; color: #888;">Type search text or modify filters to query unattached sales orders...</p>';
        document.getElementById('attachInvoiceModal').style.display = 'flex';
    }

    function closeAttachInvoiceModal() {
        document.getElementById('attachInvoiceModal').style.display = 'none';
    }

    function searchUnattachedInvoices() {
        const query = document.getElementById('invoiceSearchInput').value;
        const startDate = document.getElementById('soFilterStartDate').value;
        const endDate = document.getElementById('soFilterEndDate').value;
        const status = document.getElementById('soFilterStatus').value;
        const container = document.getElementById('unattachedInvoicesContainer');
        
        container.innerHTML = '<p style="text-align: center; color: #888; margin: 10px 0;">Searching... </p>';
        
        let url = '<?= APP_URL ?>/RepTracking/api_get_unattached_invoices?search=' + encodeURIComponent(query);
        if (startDate) url += '&start_date=' + encodeURIComponent(startDate);
        if (endDate) url += '&end_date=' + encodeURIComponent(endDate);
        if (status) url += '&status=' + encodeURIComponent(status);
        
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success' || !data.invoices || data.invoices.length === 0) {
                    container.innerHTML = '<p style="text-align: center; color: #888; margin: 10px 0;">No unattached sales orders found.</p>';
                    return;
                }
                
                let html = '<div style="display: flex; flex-direction: column; gap: 8px;">';
                data.invoices.forEach(inv => {
                    let amtFormatted = parseFloat(inv.true_grand_total).toLocaleString('en-IN', {minimumFractionDigits: 2});
                    html += `
                        <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; padding: 6px; border-bottom: 1px solid #f0f0f0; margin-bottom: 0;">
                            <input type="checkbox" class="attach-invoice-checkbox" value="${inv.id}" style="width: 16px; height: 16px;">
                            <div style="flex: 1;">
                                <div style="font-weight: bold; color: #333;">${inv.invoice_number} <span style="font-size: 10px; font-weight: bold; background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; margin-left: 8px;">${inv.status}</span></div>
                                <div style="font-size: 11px; color: #666;">Customer: <strong>${inv.customer_name}</strong> | Date: ${inv.invoice_date}</div>
                            </div>
                            <div style="font-weight: bold; font-family: monospace; color: #2e7d32;">Rs ${amtFormatted}</div>
                        </label>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            });
    }

    function confirmAttachInvoices() {
        const checkedInvoices = [];
        document.querySelectorAll('.attach-invoice-checkbox:checked').forEach(cb => {
            checkedInvoices.push(cb.value);
        });
        
        if (checkedInvoices.length === 0) { alert("Please select at least one sales order to attach."); return; }
        
        closeAttachInvoiceModal();
        
        fetchSecure('<?= APP_URL ?>/RepTracking/api_attach_invoices', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ route_id: currentRouteId, invoice_ids: checkedInvoices })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("Attached successfully!");
                loadRouteDetails(currentRouteId);
            } else {
                alert("Error: " + data.message);
            }
        });
    }

    function resetSalesOrderFilters() {
        document.getElementById('invoiceSearchInput').value = '';
        document.getElementById('soFilterStartDate').value = '';
        document.getElementById('soFilterEndDate').value = '';
        document.getElementById('soFilterStatus').value = '';
        searchUnattachedInvoices();
    }

    /* Dots Menu Handlers */
    function toggleDotsMenu(e, id) {
        e.stopPropagation();
        const btn = e.currentTarget;
        const dropdown = document.getElementById('dots-dropdown-' + id);
        if (!dropdown) return;
        
        const isShowing = dropdown.classList.contains('show');
        
        // Hide all other dropdowns
        closeAllDotsMenus();
        
        if (!isShowing) {
            const rect = btn.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            const windowWidth = window.innerWidth;
            
            // Temporarily show to measure dimensions, then hide
            dropdown.style.visibility = 'hidden';
            dropdown.style.display = 'block';
            const dropdownHeight = dropdown.offsetHeight;
            const dropdownWidth = Math.max(190, dropdown.offsetWidth || 190);
            dropdown.style.display = '';
            dropdown.style.visibility = '';
            
            // Determine vertical position
            let top, bottom;
            const spaceBelow = windowHeight - rect.bottom;
            const spaceAbove = rect.top;
            
            if (spaceBelow < dropdownHeight + 12 && spaceAbove >= dropdownHeight + 12) {
                // Not enough space below but enough above -> open upward
                top = 'auto';
                bottom = (windowHeight - rect.top + 8) + 'px';
            } else {
                // Open downward (default)
                top = (rect.bottom + 8) + 'px';
                bottom = 'auto';
            }
            
            // Determine horizontal position: right-align with button
            let left = rect.right - dropdownWidth;
            // Clamp to viewport bounds (with 8px padding)
            left = Math.max(8, Math.min(left, windowWidth - dropdownWidth - 8));
            
            dropdown.style.top = top;
            dropdown.style.bottom = bottom;
            dropdown.style.left = left + 'px';
            dropdown.style.right = 'auto';
            dropdown.style.margin = '0';
            
            dropdown.classList.add('show');
            
            const backdrop = document.getElementById('menuBackdrop');
            if (backdrop) backdrop.style.display = 'block';
        }
    }

    function closeAllDotsMenus() {
        document.querySelectorAll('.dots-dropdown').forEach(d => d.classList.remove('show'));
        const backdrop = document.getElementById('menuBackdrop');
        if (backdrop) backdrop.style.display = 'none';
        document.querySelectorAll('.dots-menu-container').forEach(c => c.style.zIndex = '');
    }

    // Close dropdowns on click outside
    document.addEventListener('click', function() {
        closeAllDotsMenus();
    });

    function updateSingleInvoiceDeliveryStatus(invoiceId, customerId, newStatus) {
        const payload = {
            route_id: parseInt(currentRouteId),
            customer_id: parseInt(customerId),
            deliveries: [
                {
                    invoice_id: parseInt(invoiceId),
                    delivery_status: newStatus,
                    items: []
                }
            ],
            collections: null
        };
        
        fetchSecure('<?= APP_URL ?>/RepTracking/api_process_delivery_visit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                loadDeliveryLiveStage(currentRouteId);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('An unexpected error occurred during status update.');
        });
    }

    function editSalesOrder(id) {
        window.open('<?= APP_URL ?>/sales/edit/' + id + '?type=sales_order&back_url=' + encodeURIComponent(window.location.href), '_blank');
    }

    function printInvoice(id) {
        window.open('<?= APP_URL ?>/sales/show/' + id + '?print=1', '_blank');
    }

    function viewCustomerProfile(customerName) {
        window.open('<?= APP_URL ?>/customers?search=' + encodeURIComponent(customerName), '_blank');
    }

    function downloadInvoicePdf(id) {
        window.open('<?= APP_URL ?>/sales/show/' + id + '?pdf=1', '_blank');
    }

    function exportInvoiceExcel(id) {
        window.open('<?= APP_URL ?>/sales/show/' + id + '?excel=1', '_blank');
    }

    /* Secure Delete Handlers */
    let deleteTargetId = null;

    function confirmDeleteSalesOrder(id, invNum) {
        deleteTargetId = id;
        document.getElementById('deleteTargetInvNum').innerText = invNum;
        document.getElementById('deleteConfirmPassword').value = '';
        document.getElementById('deleteConfirmReason').value = '';
        document.getElementById('deleteConfirmModal').style.display = 'flex';
    }

    function closeDeleteConfirmModal() {
        document.getElementById('deleteConfirmModal').style.display = 'none';
        deleteTargetId = null;
    }

    function submitDeleteSalesOrder() {
        const password = document.getElementById('deleteConfirmPassword').value;
        const reason = document.getElementById('deleteConfirmReason').value.trim();
        
        if (!password) { alert("Please enter the administrator password."); return; }
        if (!reason) { alert("Please enter a deletion reason."); return; }
        
        const targetId = deleteTargetId;
        console.log("[Delete Sales Order] Initiating deletion for ID:", targetId);
        
        closeDeleteConfirmModal();
        
        const formData = new URLSearchParams();
        formData.append('password', password);
        formData.append('delete_reason', reason);
        formData.append('is_ajax', '1');
        
        fetchSecure('<?= APP_URL ?>/sales/delete/' + targetId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData.toString()
        })
        .then(res => res.json())
        .then(data => {
            console.log("[Delete Sales Order] Response data:", data);
            if (data.status === 'success') {
                alert(data.message || "Sales Order successfully deleted and stock balances reversed!");
                onRouteDataChanged();
                loadAdjustmentsStage(currentRouteId);
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => {
            console.error("[Delete Sales Order] Fetch error:", err);
            alert("Error deleting sales order: " + err.message);
        });
    }

    /* Route Deletion Handlers */
    function scramblePassword(str, key) {
        if (!key) return str;
        let result = "";
        for (let i = 0; i < str.length; i++) {
            let charCode = str.charCodeAt(i) ^ key.charCodeAt(i % key.length);
            result += String.fromCharCode(charCode);
        }
        return btoa(result);
    }

    function refreshDeleteRouteCaptcha() {
        document.getElementById('deleteRouteCaptchaQuestion').innerText = 'Loading CAPTCHA...';
        document.getElementById('deleteRouteCaptchaAnswer').value = '';
        fetchSecure('<?= APP_URL ?>/RepTracking/get_captcha')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('deleteRouteCaptchaQuestion').innerText = data.question;
            } else {
                document.getElementById('deleteRouteCaptchaQuestion').innerText = 'Failed to load CAPTCHA';
            }
        })
        .catch(err => {
            document.getElementById('deleteRouteCaptchaQuestion').innerText = 'Error loading CAPTCHA';
        });
    }

    function openDeleteRouteModal() {
        if (!currentRouteId) { alert("No route selected!"); return; }
        const routeNumText = `#RT-${String(currentRouteId).padStart(5, '0')}`;
        document.getElementById('deleteRouteTargetNum').innerText = routeNumText;
        document.getElementById('deleteRoutePassword').value = '';
        document.getElementById('deleteRouteReason').value = '';
        document.getElementById('deleteRouteModal').style.display = 'flex';
        refreshDeleteRouteCaptcha();
    }

    function closeDeleteRouteModal() {
        document.getElementById('deleteRouteModal').style.display = 'none';
    }

    function submitDeleteRoute() {
        const password = document.getElementById('deleteRoutePassword').value;
        const reason = document.getElementById('deleteRouteReason').value.trim();
        const mode = document.querySelector('input[name="deleteRouteMode"]:checked').value;
        const captchaAnswer = document.getElementById('deleteRouteCaptchaAnswer').value.trim();
        
        if (!password) { alert("Please enter the administrator password."); return; }
        if (!captchaAnswer) { alert("Please enter the CAPTCHA answer."); return; }
        if (!reason) { alert("Please enter a deletion reason."); return; }
        
        console.log("[Delete Route] Initiating deletion for Route ID:", currentRouteId, "Mode:", mode);
        
        closeDeleteRouteModal();
        
        const scrambledPassword = scramblePassword(password, CSRF_TOKEN);
        
        const formData = new URLSearchParams();
        formData.append('route_id', currentRouteId);
        formData.append('mode', mode);
        formData.append('password', scrambledPassword);
        formData.append('delete_reason', reason);
        formData.append('captcha_answer', captchaAnswer);
        formData.append('is_ajax', '1');
        
        fetchSecure('<?= APP_URL ?>/RepTracking/delete_route', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData.toString()
        })
        .then(res => res.json())
        .then(data => {
            console.log("[Delete Route] Response data:", data);
            if (data.status === 'success') {
                alert(data.message || "Route successfully deleted!");
                goBackToRoutes();
                onRouteDataChanged();
            } else {
                alert("Error: " + data.message);
                document.getElementById('deleteRouteModal').style.display = 'flex';
                refreshDeleteRouteCaptcha();
            }
        })
        .catch(err => {
            console.error("[Delete Route] Fetch error:", err);
            alert("Error deleting route: " + err.message);
            document.getElementById('deleteRouteModal').style.display = 'flex';
            refreshDeleteRouteCaptcha();
        });
    }

    /* Move Sales Order Handlers */
    let moveTargetId = null;

    function openMoveInvoiceModal(id, invNum) {
        moveTargetId = id;
        document.getElementById('moveTargetInvNum').innerText = invNum;
        
        const select = document.getElementById('moveDestinationRouteSelect');
        select.innerHTML = '<option value="">-- Select Destination Route --</option>';
        
        document.querySelectorAll('.route-item').forEach(el => {
            const rId = el.id.replace('route_', '');
            if (parseInt(rId) === parseInt(currentRouteId)) return;
            
            const d = document.getElementById('route_data_' + rId);
            if (!d) return;
            const rName = d.getAttribute('data-rname') || '';
            const repName = d.getAttribute('data-rep') || '';
            const date = d.getAttribute('data-date') || '';
            
            const opt = document.createElement('option');
            opt.value = rId;
            opt.innerText = `#RT-${String(rId).padStart(5, '0')} - ${rName} (${repName} | ${date})`;
            select.appendChild(opt);
        });
        
        document.getElementById('moveInvoiceModal').style.display = 'flex';
    }

    function closeMoveInvoiceModal() {
        document.getElementById('moveInvoiceModal').style.display = 'none';
        moveTargetId = null;
    }

    function submitMoveSalesOrder() {
        const targetRouteId = document.getElementById('moveDestinationRouteSelect').value;
        if (!targetRouteId) { alert("Please select a destination route."); return; }
        
        const targetId = moveTargetId;
        console.log("[Move Sales Order] Initiating move for ID:", targetId, "to route:", targetRouteId);
        
        closeMoveInvoiceModal();
        
        fetchSecure('<?= APP_URL ?>/RepTracking/api_attach_invoices', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ route_id: targetRouteId, invoice_ids: ['route:' + targetId] })
        })
        .then(res => res.json())
        .then(data => {
            console.log("[Move Sales Order] Response data:", data);
            if (data.status === 'success') {
                alert("Sales Order successfully moved to the destination route!");
                onRouteDataChanged();
                loadAdjustmentsStage(currentRouteId);
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => {
            console.error("[Move Sales Order] Fetch error:", err);
            alert("Error moving sales order: " + err.message);
        });
    }



    // Explicitly export functions to global scope for inline HTML handlers
    
    // Centrally registered DOM event listeners (MED-4)
    const initCentrallyRegisteredListeners = () => {
        document.getElementById('filterRepSelect')?.addEventListener('change', (event) => { searchRouteList(); });
        document.getElementById('filterRouteSelect')?.addEventListener('change', (event) => { searchRouteList(); });
        document.getElementById('filterDateInput')?.addEventListener('change', (event) => { searchRouteList(); });
        document.getElementById('filterTerritorySelect')?.addEventListener('change', (event) => { searchRouteList(); });
        document.getElementById('auto-evt-button-1')?.addEventListener('click', (event) => { clearFilters(); });
        document.getElementById('auto-evt-button-1')?.addEventListener('mouseover', function(event) { this.style.background='var(--c-fill)'; this.style.color='var(--t-primary)';; });
        document.getElementById('auto-evt-button-1')?.addEventListener('mouseout', function(event) { this.style.background='var(--c-surface2)'; this.style.color='var(--t-secondary)';; });
        document.getElementById('auto-evt-button-2')?.addEventListener('click', (event) => { goBackToRoutes(); });
        document.getElementById('auto-evt-button-3')?.addEventListener('click', (event) => { openRouteSwitcherModal(); });
        document.getElementById('btnViewMap')?.addEventListener('click', (event) => { openMapModal(); });
        document.getElementById('auto-evt-button-4')?.addEventListener('click', function(event) { switchRouteTab(1, this); });
        document.getElementById('auto-evt-button-6')?.addEventListener('click', function(event) { switchRouteTab(3, this); });
        document.getElementById('auto-evt-button-7')?.addEventListener('click', function(event) { switchRouteTab(4, this); });
        document.getElementById('auto-evt-button-8')?.addEventListener('click', function(event) { switchRouteTab(5, this); });
        document.getElementById('auto-evt-button-9')?.addEventListener('click', function(event) { switchRouteTab(6, this); });
        document.getElementById('auto-evt-button-10')?.addEventListener('click', function(event) { switchRouteTab(7, this); });
        document.getElementById('auto-evt-button-11')?.addEventListener('click', function(event) { switchRouteTab(8, this); });
        document.getElementById('auto-evt-button-12')?.addEventListener('click', function(event) { switchRouteTab(9, this); });
        document.getElementById('auto-evt-button-13')?.addEventListener('click', function(event) { switchRouteTab(10, this); });
        document.getElementById('btnTabFinalize')?.addEventListener('click', function(event) { switchRouteTab(11, this); });
        document.getElementById('auto-evt-button-14')?.addEventListener('click', (event) => { goBackToRoutes(); });
        document.getElementById('sb-step-1')?.addEventListener('click', (event) => { switchRouteTab(1); });
        document.getElementById('sb-step-3')?.addEventListener('click', (event) => { switchRouteTab(3); });
        document.getElementById('sb-step-4')?.addEventListener('click', (event) => { switchRouteTab(4); });
        document.getElementById('sb-step-5')?.addEventListener('click', (event) => { switchRouteTab(5); });
        document.getElementById('sb-step-6')?.addEventListener('click', (event) => { switchRouteTab(6); });
        document.getElementById('sb-step-7')?.addEventListener('click', (event) => { switchRouteTab(7); });
        document.getElementById('sb-step-8')?.addEventListener('click', (event) => { switchRouteTab(8); });
        document.getElementById('sb-step-9')?.addEventListener('click', (event) => { switchRouteTab(9); });
        document.getElementById('sb-step-10')?.addEventListener('click', (event) => { switchRouteTab(10); });
        document.getElementById('sb-step-11')?.addEventListener('click', (event) => { switchRouteTab(11); });
        document.getElementById('auto-evt-button-15')?.addEventListener('click', (event) => { printBalancingReport(); });
        document.getElementById('auto-evt-button-16')?.addEventListener('click', (event) => { printLoadingSheetSpreadsheet(); });
        document.getElementById('auto-evt-button-17')?.addEventListener('click', (event) => { printLoadingSheet('summary'); });
        document.getElementById('auto-evt-button-18')?.addEventListener('click', (event) => { exportCSV(); });
        document.getElementById('auto-evt-button-19')?.addEventListener('click', (event) => { openRouteSwitcherModal(); });
        document.getElementById('btnViewMapDetails')?.addEventListener('click', (event) => { openMapModal(); });
        document.getElementById('auto-evt-button-20')?.addEventListener('click', (event) => { openDeleteRouteModal(); });
        document.getElementById('btnSaveRouteNotes')?.addEventListener('click', (event) => { saveRouteNotes(); });
        document.getElementById('btnTab3CreateSO')?.addEventListener('click', (event) => { redirectToAddInvoice(); });
        document.getElementById('btnTab3AttachSO')?.addEventListener('click', (event) => { openAttachInvoiceModal(); });
        document.getElementById('btnTab3PrintInvoices')?.addEventListener('click', (event) => { printRouteInvoices(); });
        document.getElementById('btnTab3PrintSummary')?.addEventListener('click', (event) => { printRouteInvoicesSummary(); });
        document.getElementById('auto-evt-button-21')?.addEventListener('click', (event) => { printLoadingSheet('summary'); });
        document.getElementById('auto-evt-button-22')?.addEventListener('click', (event) => { submitAdjustmentsLogisticsArrange(); });
        document.getElementById('creditBillsSearch')?.addEventListener('input', (event) => { filterCreditBillsList(); });
        document.getElementById('creditBillsRouteFilter')?.addEventListener('change', (event) => { filterCreditBillsList(); });
        document.getElementById('actQty5000')?.addEventListener('input', (event) => { recalculateDenominations(); });
        document.getElementById('actQty2000')?.addEventListener('input', (event) => { recalculateDenominations(); });
        document.getElementById('actQty1000')?.addEventListener('input', (event) => { recalculateDenominations(); });
        document.getElementById('actQty500')?.addEventListener('input', (event) => { recalculateDenominations(); });
        document.getElementById('actQty100')?.addEventListener('input', (event) => { recalculateDenominations(); });
        document.getElementById('actQty50')?.addEventListener('input', (event) => { recalculateDenominations(); });
        document.getElementById('actQty20')?.addEventListener('input', (event) => { recalculateDenominations(); });
        document.getElementById('actValCoins')?.addEventListener('input', (event) => { recalculateDenominations(); });
        document.getElementById('btnSaveReconciliationDraft')?.addEventListener('click', (event) => { saveReconciliationDraft(); });
        document.getElementById('settleVerifyStock')?.addEventListener('change', (event) => { checkSettleVerification(); updateSidebarProgress(); });
        document.getElementById('btnSaveReturnStockDraft')?.addEventListener('click', (event) => { saveReturnStockDraft(); });
        document.getElementById('btnPrintReturnStock')?.addEventListener('click', (event) => {
            if (!currentRouteId) return;
            window.open('<?= APP_URL ?>/RepTracking/print_return_stock/' + currentRouteId, '_blank');
        });
        document.getElementById('settleDeTabCollectionsBtn')?.addEventListener('click', (event) => { switchSettleDeTab('collections'); });
        document.getElementById('settleDeTabSalesBtn')?.addEventListener('click', (event) => { switchSettleDeTab('sales'); });
        document.getElementById('btnSaveAccountingDraft')?.addEventListener('click', (event) => { saveAccountingDraft(); });
        document.getElementById('settleSubmitBtn')?.addEventListener('click', (event) => { submitFinalSettle(); });
        document.getElementById('auto-evt-button-23')?.addEventListener('click', (event) => { printBalancingReport(); });
        document.getElementById('auto-evt-button-24')?.addEventListener('click', (event) => { printLoadingSheetSpreadsheet(); });
        document.getElementById('auto-evt-button-25')?.addEventListener('click', (event) => { printLoadingSheet('summary'); });
        document.getElementById('auto-evt-button-26')?.addEventListener('click', (event) => { exportCSV(); });
        document.getElementById('compTabInvoicesBtn')?.addEventListener('click', (event) => { switchCompletedTab('invoices'); });
        document.getElementById('compTabCollectionsBtn')?.addEventListener('click', (event) => { switchCompletedTab('collections'); });
        document.getElementById('compTabVariancesBtn')?.addEventListener('click', (event) => { switchCompletedTab('variances'); });
        document.getElementById('auto-evt-div-27')?.addEventListener('click', (event) => { document.getElementById('floatingSearchInput').focus(); });
        document.getElementById('floatingSearchInput')?.addEventListener('input', (event) => { searchRouteList(); });
        document.getElementById('auto-evt-button-28')?.addEventListener('click', (event) => { openCreateRouteModal(); });
        document.getElementById('btnOpenRouteBinding')?.addEventListener('click', (event) => { openRouteBindingModal(); });
        document.getElementById('btnUnbindRoute')?.addEventListener('click', (event) => { unbindActiveRoute(); });
        document.getElementById('auto-evt-button-29')?.addEventListener('click', (event) => { window.location.reload(); });
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCentrallyRegisteredListeners);
    } else {
        initCentrallyRegisteredListeners();
    }
window.buildAccountOptions = buildAccountOptions;
    window.getAccountIdByCode = getAccountIdByCode;
    window.fetchRoutesList = fetchRoutesList;
    window.changePage = changePage;
    window.filterLeftPane = filterLeftPane;
    window.searchRouteList = searchRouteList;
    window.clearFilters = clearFilters;
    window.openRouteSwitcherModal = openRouteSwitcherModal;
    window.closeRouteSwitcherModal = closeRouteSwitcherModal;
    window.openCreateRouteModal = openCreateRouteModal;
    window.closeCreateRouteModal = closeCreateRouteModal;
    window.searchRouteSwitcherList = searchRouteSwitcherList;
    window.selectRouteFromSwitcher = selectRouteFromSwitcher;
    window.goBackToRoutes = goBackToRoutes;
    window.fetchSecure = fetchSecure;
    window.onRouteDataChanged = onRouteDataChanged;
    window.updateSidebarProgress = updateSidebarProgress;
    window.updateWizardProgress = updateWizardProgress;
    window.loadRouteDetails = loadRouteDetails;
    window.switchRouteTab = switchRouteTab;
    window.loadTab1Details = loadTab1Details;
    window.saveRouteNotes = saveRouteNotes;

    window.loadOutstandingBillsChecklist = loadOutstandingBillsChecklist;
    window.filterCreditBillsList = filterCreditBillsList;
    window.toggleCreditBillSelection = toggleCreditBillSelection;
    window.loadAdjustmentsStage = loadAdjustmentsStage;
    window.detachInvoice = detachInvoice;
    window.submitAdjustmentsLogisticsArrange = submitAdjustmentsLogisticsArrange;
    window.loadLoadingStage = loadLoadingStage;
    window.loadVarianceAdjustmentStage = loadVarianceAdjustmentStage;
    window.renderVarianceReconciliation = renderVarianceReconciliation;
    window.updateRemoveCompletelyChoice = updateRemoveCompletelyChoice;
    window.applyProductSubstitution = applyProductSubstitution;
    window.updateInvoiceAllocation = updateInvoiceAllocation;
    window.autoDistributeVariance = autoDistributeVariance;
    window.submitVarianceAdjustments = submitVarianceAdjustments;
    window.printLoadingSheet = printLoadingSheet;
    window.loadDispatchStage = loadDispatchStage;
    window.loadDeliveryLiveStage = loadDeliveryLiveStage;
    window.openServerDeliveryProcessModal = openServerDeliveryProcessModal;
    window.filterSdpItems = filterSdpItems;
    window.updateSdpBalance = updateSdpBalance;
    window.recalculateSdpInvoiceTotal = recalculateSdpInvoiceTotal;
    window.closeServerDeliveryProcessModal = closeServerDeliveryProcessModal;
    window.addSdpChequeRow = addSdpChequeRow;
    window.submitServerDeliveryProcess = submitServerDeliveryProcess;
    window.applyDefensiveGuard = applyDefensiveGuard;
    window.loadTab8Reconciliation = loadTab8Reconciliation;
    window.recalculateDenominations = recalculateDenominations;
    window.calculateCashVariance = calculateCashVariance;
    window.toggleReconChequeApproval = toggleReconChequeApproval;
    window.saveReconciliationDraft = saveReconciliationDraft;
    window.loadTab9ReturnStock = loadTab9ReturnStock;
    window.saveReturnStockDraft = saveReturnStockDraft;
    window.loadTab10Accounting = loadTab10Accounting;
    window.loadTab11Finalize = loadTab11Finalize;
    window.saveAccountingDraft = saveAccountingDraft;
    window.checkSettleVerification = checkSettleVerification;
    window.switchSettleDeTab = switchSettleDeTab;
    window.renderSettleDeAccountSelect = renderSettleDeAccountSelect;
    window.renderSettleDoubleEntries = renderSettleDoubleEntries;
    window.submitFinalSettle = submitFinalSettle;
    window.openBalancingReportModal = openBalancingReportModal;
    window.closeBalancingReportModal = closeBalancingReportModal;
    window.printBalancingReportFromModal = printBalancingReportFromModal;
    window.printBalancingReport = printBalancingReport;
    window.printLoadingSheetSpreadsheet = printLoadingSheetSpreadsheet;
    window.exportCSV = exportCSV;
    window.getDataTypeFromStatus = getDataTypeFromStatus;
    window.advanceRouteStatus = advanceRouteStatus;
    window.redirectToAddInvoice = redirectToAddInvoice;
    window.printRouteInvoices = printRouteInvoices;
    window.printRouteInvoicesSummary = printRouteInvoicesSummary;
    window.openInvoiceSlider = openInvoiceSlider;
    window.closeInvoiceSlider = closeInvoiceSlider;
    window.deleteSalesOrder = deleteSalesOrder;
    window.openMapModal = openMapModal;
    window.closeMapModal = closeMapModal;
    window.initRoutePathMap = initRoutePathMap;
    window.clearRoutePathMap = clearRoutePathMap;
    window.loadRoutePath = loadRoutePath;
    window.renderRoutePath = renderRoutePath;
    window.getEligibleBindingRoutes = getEligibleBindingRoutes;
    window.openRouteBindingModal = openRouteBindingModal;
    window.closeRouteBindingModal = closeRouteBindingModal;
    window.addBindingSlot = addBindingSlot;
    window.removeBindingSlot = removeBindingSlot;
    window.onBindingSlotRouteSelect = onBindingSlotRouteSelect;
    window.submitRouteBinding = submitRouteBinding;
    window.unbindActiveRoute = unbindActiveRoute;
    window.unbindCombinedRoute = unbindCombinedRoute;
    window.openAttachInvoiceModal = openAttachInvoiceModal;
    window.closeAttachInvoiceModal = closeAttachInvoiceModal;
    window.searchUnattachedInvoices = searchUnattachedInvoices;
    window.confirmAttachInvoices = confirmAttachInvoices;
    window.resetSalesOrderFilters = resetSalesOrderFilters;
    window.toggleDotsMenu = toggleDotsMenu;
    window.closeAllDotsMenus = closeAllDotsMenus;
    window.updateSingleInvoiceDeliveryStatus = updateSingleInvoiceDeliveryStatus;
    window.editSalesOrder = editSalesOrder;
    window.printInvoice = printInvoice;
    window.viewCustomerProfile = viewCustomerProfile;
    window.downloadInvoicePdf = downloadInvoicePdf;
    window.exportInvoiceExcel = exportInvoiceExcel;
    window.confirmDeleteSalesOrder = confirmDeleteSalesOrder;
    window.closeDeleteConfirmModal = closeDeleteConfirmModal;
    window.submitDeleteSalesOrder = submitDeleteSalesOrder;
    window.scramblePassword = scramblePassword;
    window.refreshDeleteRouteCaptcha = refreshDeleteRouteCaptcha;
    window.openDeleteRouteModal = openDeleteRouteModal;
    window.closeDeleteRouteModal = closeDeleteRouteModal;
    window.submitDeleteRoute = submitDeleteRoute;
    window.openMoveInvoiceModal = openMoveInvoiceModal;
    window.closeMoveInvoiceModal = closeMoveInvoiceModal;
    window.submitMoveSalesOrder = submitMoveSalesOrder;
})();
</script>