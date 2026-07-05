<?php
$content = file_get_contents('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Views/rep-tracking/index.php');
preg_match_all('/class="[^"]*tab[^"]*"|class=\'[^\']*tab[^\']*\'/i', $content, $matches, PREG_OFFSET_CAPTURE);
// Let's just find the menu or tabs container HTML
preg_match_all('/<ul[^>]*class="[^"]*nav[^"]*"[^>]*>/i', $content, $matches, PREG_OFFSET_CAPTURE);
if (empty($matches[0])) {
    preg_match_all('/<div[^>]*class="[^"]*tab[^"]*"[^>]*>/i', $content, $matches, PREG_OFFSET_CAPTURE);
}
// Let's print out lines around the tab menu
// We can find where "<a" or "<button" or "tab-link" or "tablink" is defined.
preg_match_all('/tab-link|tablink/i', $content, $matches, PREG_OFFSET_CAPTURE);
$shown = [];
foreach ($matches[0] as $match) {
    $offset = $match[1];
    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
    if (!isset($shown[$line])) {
        $shown[$line] = true;
        $lines = explode("\n", $content);
        echo "Line $line: " . trim($lines[$line - 1]) . "\n";
    }
}
