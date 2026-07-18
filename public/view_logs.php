<?php
header('Content-Type: text/plain');
$logFile = dirname(__DIR__) . '/app_errors.log';
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    $lines = explode("\n", $content);
    $last_lines = array_slice($lines, -50);
    echo implode("\n", $last_lines);
} else {
    echo "Log file not found at " . $logFile;
}
