<?php
$lines = file('../app/Views/petty_cash/index.php');
$found = false;
foreach ($lines as $i => $line) {
    if (strpos($line, 'foreach ($data[\'transactions\']') !== false || strpos($line, 'foreach($data[\'transactions\']') !== false) {
        $found = true;
        $start = $i;
    }
    if ($found && $i - $start < 60) {
        echo "Line " . ($i + 1) . ": " . $line;
    }
}
