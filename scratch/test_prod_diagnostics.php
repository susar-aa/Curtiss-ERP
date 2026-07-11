<?php
$url = "https://curtiss.suzxlabs.com/diagnostics/db?secret=curtiss_debug_123";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n\n";
echo "Response snippet:\n";
echo substr($response, 0, 1000) . "\n...\n";
if (strlen($response) > 1000) {
    echo "Tail of response:\n";
    echo substr($response, -1000) . "\n";
}
