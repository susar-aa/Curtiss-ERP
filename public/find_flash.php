<?php
$lines = file('../app/Views/petty_cash/index.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'flash_') !== false || strpos($line, 'SESSION') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
