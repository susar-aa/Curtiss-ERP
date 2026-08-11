<?php
$c = file_get_contents('app/Views/sales/invoice_view.php');
preg_match('/<style>.*?<\/style>/s', $c, $matches);
file_put_contents('scratch_style.txt', $matches[0]);
