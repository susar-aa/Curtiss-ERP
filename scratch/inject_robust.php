<?php
$content = file_get_contents('app/Views/suppliers/index.php');
$js = file_get_contents('scratch/robust.js');
$new_content = preg_replace('/<script>.*?<\/script>/s', "<script>\n" . $js . "\n</script>", $content);
file_put_contents('app/Views/suppliers/index.php', $new_content);
