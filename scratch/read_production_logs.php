<?php
$url = "https://curtiss.suzxlabs.com/diagnostics/db?secret=curtiss_debug_123";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

if (preg_match_all('/<div class="log-viewer"[^>]*>(.*?)<\/div>/is', $response, $matches)) {
    echo "=== PRODUCTION APP ERRORS ===\n";
    echo isset($matches[1][0]) ? html_entity_decode(strip_tags($matches[1][0])) : "None found";
    echo "\n\n=== PRODUCTION SYNC ERRORS ===\n";
    echo isset($matches[1][1]) ? html_entity_decode(strip_tags($matches[1][1])) : "None found";
    echo "\n";
} else {
    echo "Failed to extract log-viewer blocks. Response was:\n";
    echo substr($response, 0, 5000);
}
