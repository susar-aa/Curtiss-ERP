<?php
$f = 'app/Views/auth/login.php';
$c = file_get_contents($f);
$c = str_replace("</body>\r\n    <?php if(isset(\$data['debug_console'])): ?>", "    <?php if(isset(\$data['debug_console'])): ?>", $c);
$c = str_replace("</body>\n    <?php if(isset(\$data['debug_console'])): ?>", "    <?php if(isset(\$data['debug_console'])): ?>", $c);
file_put_contents($f, $c);
echo "Fixed";
