<?php
$html = file_get_contents(__DIR__ . '/rendered_petty_cash.html');
echo "Size: " . strlen($html) . " bytes\n";
$ids = ['settingsModal', 'allocateModal', 'expenseModal', 'reimburseModal'];
foreach ($ids as $id) {
    echo "ID '$id': " . (strpos($html, 'id="' . $id . '"') !== false ? 'YES' : 'NO') . "\n";
}
