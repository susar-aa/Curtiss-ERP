<?php
$logPath = "C:\\Users\\Susara Senarathne\\.gemini\\antigravity\\brain\\e0deb26c-8efe-4b0a-94ef-3601e2a364e3\\.system_generated\\logs\\overview.txt";
if (!file_exists($logPath)) {
    die("Log file not found.\n");
}
$content = file_get_contents($logPath);
$lines = explode("\n", $content);
echo "Total lines: " . count($lines) . "\n";
$matches = 0;
foreach ($lines as $i => $line) {
    if (stripos($line, 'sync') !== false || stripos($line, 'response') !== false || stripos($line, '500') !== false) {
        echo "Line " . ($i + 1) . ": " . substr($line, 0, 150) . "\n";
        $matches++;
        if ($matches > 100) {
            echo "... truncated ...\n";
            break;
        }
    }
}
