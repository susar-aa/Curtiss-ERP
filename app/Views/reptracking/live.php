<!-- Leaflet Map CSS and JS for gorgeous fallback OpenStreetMap visualizer -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<!-- Firebase App & Database Compat SDKs -->
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-database-compat.js"></script>

<style>
    :root {
        --c-active: #10b981;
        --c-inactive: #64748b;
        --c-card-bg: rgba(255, 255, 255, 0.9);
        --c-border: #cbd5e1;
    }
    
    .tracking-wrapper {
        display: flex;
        height: calc(100vh - 120px);
        margin: -20px;
        font-family: "Outfit", "Inter", sans-serif;
    }

    .tracking-sidebar {
        width: 320px;
        background: #ffffff;
        border-right: 1px solid var(--c-border);
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        z-index: 10;
        box-shadow: 2px 0 15px rgba(0,0,0,0.03);
    }

    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }

    .rep-list {
        flex: 1;
        overflow-y: auto;
        padding: 10px;
        list-style: none;
        margin: 0;
    }

    .rep-item {
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 8px;
        border: 1px solid #f1f5f9;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        background: #fff;
    }

    .rep-item:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }

    .rep-item.active {
        background: #e8f5e9;
        border-color: #a5d6a7;
    }

    .rep-name {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
    
    .status-dot.online {
        background: var(--c-active);
        box-shadow: 0 0 8px var(--c-active);
        animation: pulse 1.5s infinite;
    }
    
    .status-dot.offline {
        background: var(--c-inactive);
    }

    .rep-meta {
        font-size: 11px;
        color: #64748b;
        margin-top: 4px;
    }

    .map-container {
        flex: 1;
        position: relative;
        background: #e2e8f0;
    }

    #liveMap {
        width: 100%;
        height: 100%;
    }

    .config-card {
        padding: 15px;
        border-top: 1px solid var(--c-border);
        background: #f8fafc;
    }

    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
</style>

<div class="tracking-wrapper">
    <!-- Sidebar Panel -->
    <div class="tracking-sidebar">
        <div class="sidebar-header">
            <h3 style="margin: 0; font-size: 16px; color:#0f172a;"><i class="ph ph-radar-fill" style="color:var(--c-active);"></i> Live Representatives</h3>
            <p style="margin: 5px 0 0 0; font-size: 12px; color: #64748b;">Real-time GPS tracking status feed</p>
        </div>

        <ul class="rep-list" id="repList">
            <?php foreach ($data['reps'] as $r): ?>
                <li class="rep-item" id="rep-<?= $r->id ?>" onclick="selectRepresentative(<?= $r->id ?>, '<?= htmlspecialchars($r->username) ?>')">
                    <div class="rep-name">
                        <span><?= htmlspecialchars($r->first_name . ' ' . $r->last_name) ?> (@<?= htmlspecialchars($r->username) ?>)</span>
                        <span class="status-dot offline" id="dot-<?= $r->id ?>"></span>
                    </div>
                    <div class="rep-meta" id="meta-<?= $r->id ?>">Status: Offline</div>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Firebase & Maps Config Dashboard panel -->
        <div class="config-card">
            <label style="display:block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 6px;">FIREBASE DATABASE URL</label>
            <input type="text" id="firebaseDbUrl" class="form-control" style="font-size: 12px; padding: 6px; width: 100%; box-sizing: border-box;" value="<?= htmlspecialchars($data['firebase_db_url']) ?>" onchange="reconnectFirebase()">
            <small style="font-size: 10px; color:#64748b; margin-top: 4px; display: block;">Modifications reconnect the sockets instantly.</small>
        </div>
    </div>

    <!-- Map Screen Area -->
    <div class="map-container">
        <div id="liveMap"></div>
    </div>
</div>

<script>
    let map;
    let markers = {};
    let repLocations = {};
    let activeRepId = null;
    let firebaseApp = null;
    let databaseRef = null;

    // Initialize Leaflet Map
    function initMap() {
        // Standard center point coordinates (Colombo, Sri Lanka)
        map = L.map('liveMap').setView([6.9271, 79.8612], 9);
        
        // Add beautiful responsive OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
    }

    // Connect / Initialize Firebase Realtime Database
    function connectFirebase() {
        const dbUrl = document.getElementById('firebaseDbUrl').value.trim();
        if (!dbUrl) return;

        try {
            // Delete previous firebase app if exists
            if (firebaseApp) {
                firebaseApp.delete();
            }

            const firebaseConfig = {
                databaseURL: dbUrl
            };

            firebaseApp = firebase.initializeApp(firebaseConfig, "LiveTrackingApp");
            const db = firebaseApp.database();
            databaseRef = db.ref('locations');

            // Listen to real-time location stream changes
            databaseRef.on('value', (snapshot) => {
                const data = snapshot.val();
                if (!data) return;

                Object.keys(data).forEach(key => {
                    // key format is rep_ID (e.g. rep_15)
                    const parts = key.split('_');
                    if (parts.length < 2) return;
                    const repId = parseInt(parts[1]);
                    const repData = data[key];

                    updateRepStatusAndMarker(repId, repData);
                });
            });

            console.log("Firebase stream connected to URL: " + dbUrl);
        } catch (e) {
            console.error("Firebase connection error: ", e);
            alert("Firebase Error: " + e.message);
        }
    }

    function reconnectFirebase() {
        if (databaseRef) {
            databaseRef.off();
        }
        connectFirebase();
    }

    // Update Representative visual item status and map marker position
    function updateRepStatusAndMarker(repId, data) {
        const dot = document.getElementById(`dot-${repId}`);
        const meta = document.getElementById(`meta-${repId}`);
        const repItem = document.getElementById(`rep-${repId}`);

        if (!dot || !meta) return;

        const isOnline = data.status === 'Active Route' && (Date.now() - data.timestamp) < 300000; // Online if updated within last 5 minutes

        if (isOnline) {
            dot.className = 'status-dot online';
            const updatedTime = new Date(data.timestamp).toLocaleTimeString();
            meta.innerHTML = `<strong>Online</strong> (Updated: ${updatedTime})<br>Lat: ${data.latitude.toFixed(4)}, Lng: ${data.longitude.toFixed(4)}`;
            
            repLocations[repId] = [data.latitude, data.longitude];
            
            // Marker Icon style
            const activeIcon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="background-color:#10b981; width:15px; height:15px; border-radius:50%; border:2px solid #fff; box-shadow:0 0 10px rgba(0,0,0,0.3); animation:pulse 1.5s infinite;"></div>`,
                iconSize: [15, 15]
            });

            // Update or create marker on map
            if (markers[repId]) {
                markers[repId].setLatLng([data.latitude, data.longitude]);
            } else {
                markers[repId] = L.marker([data.latitude, data.longitude], {icon: activeIcon})
                    .addTo(map)
                    .bindPopup(`<strong>${data.full_name} (@${data.username})</strong><br>Status: Online (Active Route)<br>Last Update: ${updatedTime}`);
            }

            // Pan map to active representative
            if (activeRepId === repId) {
                map.setView([data.latitude, data.longitude], 14);
            }

        } else {
            dot.className = 'status-dot offline';
            meta.innerHTML = `Offline (Last status: ${data.status || 'Offline'})`;
            
            if (markers[repId]) {
                map.removeLayer(markers[repId]);
                delete markers[repId];
            }
            delete repLocations[repId];
        }
    }

    // Select representative from sidebar to locate on map
    function selectRepresentative(repId, username) {
        // Toggle active list item classes
        document.querySelectorAll('.rep-item').forEach(el => el.classList.remove('active'));
        const activeItem = document.getElementById(`rep-${repId}`);
        if (activeItem) {
            activeItem.classList.add('active');
        }

        activeRepId = repId;

        // Pan to location if online coordinates exist
        if (repLocations[repId]) {
            const loc = repLocations[repId];
            map.setView(loc, 14);
            if (markers[repId]) {
                markers[repId].openPopup();
            }
        } else {
            alert(`Representative @${username} is currently offline or has no live coordinates.`);
        }
    }

    // Initialize module on window load
    window.onload = function() {
        initMap();
        connectFirebase();
    };
</script>
