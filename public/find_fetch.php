<?php
$lines = file('../app/Views/petty_cash/index.php');
$in_script = false;
foreach ($lines as $i => $line) {
    if (strpos($line, '--- Resilient Form Submission Payload ---') !== false) {
        $in_script = true;
    }
    if ($in_script) {
        echo "Line " . ($i + 1) . ": " . $line;
        if (strpos($line, '</script>') !== false) {
            $in_script = false;
        }
    }
}
