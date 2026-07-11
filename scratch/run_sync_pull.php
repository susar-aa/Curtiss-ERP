<?php
$userIds = [15, 16, 17, 20, 24];
foreach ($userIds as $uid) {
    echo "====================================\n";
    echo "Testing User ID: $uid\n";
    $output = shell_exec("php " . __DIR__ . "/run_single_sync.php $uid 2>&1");
    // Print first 500 chars of output to see if it's success or error
    echo substr($output, 0, 500) . "\n";
}
