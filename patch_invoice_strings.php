<?php
$f = 'app/Views/estimates/show.php';
$c = file_get_contents($f);

$c = str_replace('<span>Official Invoice View</span>', '<span>Official Estimate View</span>', $c);
$c = str_replace('<div class="document-title">Invoice</div>', '<div class="document-title">Estimate</div>', $c);
$c = str_replace('<th>Invoice No:</th>', '<th>Estimate No:</th>', $c);
$c = str_replace('$data[\'estimate\']->invoice_date', '$data[\'estimate\']->estimate_date', $c);
$c = str_replace('<div>Official Digital Invoice</div>', '<div>Official Digital Estimate</div>', $c);
$c = str_replace(">INVOICE<", ">ESTIMATE<", $c);
$c = str_replace('<strong>Invoice No:</strong>', '<strong>Estimate No:</strong>', $c);
$c = str_replace('Current Invoice Total:', 'Current Estimate Total:', $c);
$c = str_replace('link.download = \'Invoice_', 'link.download = \'Estimate_', $c);

file_put_contents($f, $c);
echo "Patched remaining invoice strings.";
