<?php
$file = 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Views/sales/index.php';
$content = file_get_contents($file);

// Check if already injected
if (strpos($content, 'LiveStockSyncApp') !== false) {
    echo "Already injected.\n";
    exit;
}

$scriptInjection = <<<HTML

<!-- Firebase Realtime Stock Sync -->
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-database-compat.js"></script>
<script>
    (function() {
        try {
            const firebaseConfig = {
                databaseURL: "https://curtiss-erp-cc0c0-default-rtdb.firebaseio.com/"
            };
            if (!firebase.apps.length) {
                firebase.initializeApp(firebaseConfig, "LiveStockSyncApp");
            } else {
                firebase.app(); // if already initialized
            }
            
            const db = firebase.app("LiveStockSyncApp").database();
            const stockRef = db.ref('stock_levels');
            
            let searchTimeout = null;
            function handleStockUpdate(snapshot) {
                const productId = snapshot.key;
                const data = snapshot.val();
                if (!data) return;
                
                // Update catalog array in memory
                if (typeof catalog !== 'undefined') {
                    let stockUpdated = false;
                    for (let i = 0; i < catalog.length; i++) {
                        let parts = catalog[i].id.split('|');
                        if (parts[0] == productId) {
                            const available = parseFloat(data.stock_qty) - (parseFloat(data.reserved_qty) || 0);
                            if (catalog[i].stock !== available) {
                                catalog[i].stock = available;
                                stockUpdated = true;
                            }
                        }
                    }
                    
                    if (stockUpdated && typeof performSearch === 'function') {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(() => {
                            performSearch();
                        }, 300);
                    }
                }
            }
            
            stockRef.on('child_added', handleStockUpdate);
            stockRef.on('child_changed', handleStockUpdate);
        } catch(e) {
            console.error("Firebase Stock Sync Error:", e);
        }
    })();
</script>
HTML;

file_put_contents($file, $content . $scriptInjection);
echo "Injected Firebase Stock Sync into app/Views/sales/index.php";
