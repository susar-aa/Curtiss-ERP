<?php
$content = file_get_contents('app/Views/suppliers/index.php');
$new_content = preg_replace('/<script>.*?<\/script>/s', '<script src="<?= APP_URL ?>/js/suppliers.js?v=' . time() . '"></script>', $content);
file_put_contents('app/Views/suppliers/index.php', $new_content);
