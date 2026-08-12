<?php
$f = 'app/Views/estimates/edit.php';
$c = file_get_contents($f);

// 1. Remove the previously injected block
$blockStart = "    // --- LOAD EXISTING DATA ---";
$blockEnd = "    // ═══ 1. REALTIME CUSTOMER SEARCH & SELECTION ═══";

$posStart = strpos($c, $blockStart);
$posEnd = strpos($c, $blockEnd);

if ($posStart !== false && $posEnd !== false) {
    $blockToMove = substr($c, $posStart, $posEnd - $posStart);
    $c = str_replace($blockToMove, '', $c);
    
    // 2. Inject it before </script>
    $c = str_replace('</script>', $blockToMove . "\n</script>", $c);
    file_put_contents($f, $c);
    echo "Fixed JS block position.";
} else {
    echo "Block not found.";
}
