<?php
$file = 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Views/sales/index.php';
$content = file_get_contents($file);

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
            
            stockRef.on('child_changed', (snapshot) => {
                const productId = snapshot.key;
                const data = snapshot.val();
                if (!data) return;
                
                // Update catalog array in memory
                if (typeof catalog !== 'undefined') {
                    let stockUpdated = false;
                    for (let i = 0; i < catalog.length; i++) {
                        let parts = catalog[i].id.split('|');
                        if (parts[0] == productId) {
                            // If it's a variation, we technically only got the parent's overall stock, 
                            // but for lack of variation-specific RTDB nodes, we update the base stock.
                            // Ideally, backend should push variation-specific stock as well.
                            // We just set it to the new available stock.
                            const available = parseFloat(data.stock_qty) - parseFloat(data.reserved_qty);
                            catalog[i].stock = available;
                            stockUpdated = true;
                        }
                    }
                    
                    if (stockUpdated && typeof performSearch === 'function') {
                        // Re-trigger search to update UI if user is currently searching
                        performSearch();
                    }
                }
            });
        } catch(e) {
            console.error("Firebase Stock Sync Error:", e);
        }
    })();
</script>
HTML;

$content = str_replace('</body>', $scriptInjection . "\n</body>", $content);

file_put_contents($file, $content);
echo "Updated sales index.php";
