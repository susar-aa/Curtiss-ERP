<?php
$content = file_get_contents('app/Views/suppliers/index.php');
if (preg_match('/<script>(.*?)<\/script>/s', $content, $matches)) {
    file_put_contents('scratch/test.js', $matches[1]);
}
