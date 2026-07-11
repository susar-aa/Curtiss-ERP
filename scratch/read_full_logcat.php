<?php
$logPath = "C:\\Users\\Susara Senarathne\\.gemini\\antigravity\\brain\\e0deb26c-8efe-4b0a-94ef-3601e2a364e3\\.system_generated\\logs\\overview.txt";
if (!file_exists($logPath)) {
    die("Log file not found.\n");
}
$content = file_get_contents($logPath);
$lines = explode("\n", $content);
echo "Total lines: " . count($lines) . "\n";

$inLogcat = false;
$logcatLines = [];
foreach ($lines as $i => $line) {
    if (strpos($line, 'PROCESS STARTED') !== false || strpos($line, 'hiddenapi:') !== false || strpos($line, 'SyncManager') !== false) {
        // Print lines around this
        echo "--- Match on line " . ($i + 1) . " ---\n";
        for ($j = max(0, $i - 10); $j < min(count($lines), $i + 40); $j++) {
            echo ($j + 1) . ": " . substr($lines[$j], 0, 200) . "\n";
        }
        echo "\n";
        $i += 40; // skip a bit
    }
}
